<?php
namespace App\Http\Controllers\Api;

use App\Billing;
use App\Pengurus;
use App\GenerateSubscribeOrder;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\DB;
use App\Kab;
use App\Kec;
use App\Kel;
use App\PaketLangganan;
use App\Payment;
use App\Warga;
use App\Wilayah;
use App\Notifikasi;
use App\Voucher;
use App\VoucherWil;
use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Response;
use Mail;
use PDF;

class BillingController extends Controller
{

    public function list(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $billing = new Billing;
        $list = $billing->getList($wil_id);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $list;

		// return json response
		return response()->json($response);

    }

    public function list_pembayaran(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $billing = new Billing;
        $list = $billing->getListPembayaran($wil_id);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $list;

		// return json response
		return response()->json($response);

    }


    public function list_pembayaran_limited(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $billing = new Billing;

        $limit = empty($request->limit) ? 20 : $request->limit;
        $page = $request->page;

        $list = $billing->getListPembayaran($wil_id, $page, $limit);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $list;

		// return json response
		return response()->json($response);

    }

    public function berhenti_berlangganan(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $wil_alasan_berhenti = $request->wil_alasan_berhenti;
        $wil_alasan_lain = $request->wil_alasan_lain;

        $wilayah = Wilayah::find($wil_id);
        $wilayah->wil_id = $wil_id;
        $wilayah->wil_alasan_berhenti = $wil_alasan_berhenti;
        $wilayah->wil_alasan_lain = $wil_alasan_lain;
        $wilayah->wil_req_berhenti = Carbon::now();
        $wilayah->wil_status = '6';
        $wilayah->save();

        // response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
    }


    public function recent_billing(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $billing = new Billing;
        $list = $billing->getRecentBilling($wil_id);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $list;

		// return json response
		return response()->json($response);

    }

    public function total_tagihan(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $billing = new Billing;
        $totalTagihan = $billing->getTotalTagihan($wil_id);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['totalTagihan'] = $totalTagihan;

		// return json response
		return response()->json($response);
    }

    public function rincian_pembayaran(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $pl_id = $request->pl_id;
        $bil_id = $request->bil_id;

        $nominal_discount = 0;
        $txtDiscount = '';

        $paketLangganan = PaketLangganan::find($pl_id);
        $billing = Billing::find($bil_id);
        //Lihat Voucher Terlebih Dahulu
        $voucherWil = VoucherWil::where('wil_id',$wil_id)
                ->where('vw_status','0')
                ->whereRaw("vw_berlaku_sd >= '".date('Y-m-d')."'")
                ->orderBy('vw_id','asc')
                ->limit(1)
                ->first();

        if(!empty($voucherWil)) {
            $voucher = Voucher::find($voucherWil->v_id);
            $nominal_discount = !empty($voucher->v_nilai_nominal) ? $voucher->v_nilai_nominal : ($voucher->v_nilai_persen * $billing->bil_jumlah/100);

            $txtDiscount = !empty($voucher->v_nilai_nominal) ? '' : $voucher->v_nilai_persen.' %';
        }

        $data = array();
        $data['nilai_paket'] = $billing->bil_jumlah;
        $data['nama_paket'] = $paketLangganan->pl_nama;
        $data['maks_warga'] = $paketLangganan->pl_maks_warga;
        $data['month_paket'] = $paketLangganan->pl_bulan;
        $data['nilai_diskon'] = $nominal_discount;
        $data['txt_diskon'] = $txtDiscount;
        $data['total_pembayaran'] = ($billing->bil_jumlah - $nominal_discount);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $data;

		// return json response
		return response()->json($response);
    }

    public function generate_payment_url_renewal_with_voucher(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());
        $pl_id = $request->pl_id;
        $bil_id = $request->bil_id;
        $wil_id = $request->wil_id;
        $warga_id = $request->warga_id;

        $paymentUrl = '';

        //Lihat Voucher Terlebih Dahulu
        $voucherWil = VoucherWil::where('wil_id',$wil_id)
                ->where('vw_status','0')
                ->whereRaw("vw_berlaku_sd >= '".date('Y-m-d')."'")
                ->orderBy('vw_id','asc')
                ->limit(1)
                ->first();

        $vw_id = null;
        $nominal_discount = 0;
        $paketLangganan = PaketLangganan::find($pl_id);
        $billing = Billing::find($bil_id);

        if(!empty($voucherWil)) {
            $voucher = Voucher::find($voucherWil->v_id);
            $vw_id = $voucherWil->vw_id;
            $nominal_discount = !empty($voucher->v_nilai_nominal) ? $voucher->v_nilai_nominal : ($voucher->v_nilai_persen * $billing->bil_jumlah/100);
        }

        $order_no = 'ES'.Carbon::now()->timestamp;
        //Generate Order
        GenerateSubscribeOrder::create([
            'order_no' => $order_no,
            'pl_id' => $pl_id,
            'wil_id' => $wil_id,
            'bil_id' => $bil_id,
            'warga_id' => $warga_id,
            'created_date' => Carbon::now(),
            'vw_id' => $vw_id,
            'nominal_discount' => $nominal_discount,
        ]);

        $total_pembayaran = ($billing->bil_jumlah - $nominal_discount);
        if($total_pembayaran <= 0) {
            // response
            $response['status'] = "failed";
            $response['message'] = "Nilai pembayaran tidak boleh nol atau minus";
            // return json response
            return response()->json($response);
        }

        $warga = Warga::find($warga_id);
        $wilayah = Wilayah::find($wil_id);

        $paymentUrl = Payment::getPaymentUrl($order_no, $total_pembayaran, $wilayah->wil_nama.' ('.'Paket '.$paketLangganan->pl_nama.')', $warga->warga_email);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['paymentUrl'] = $paymentUrl;

		// return json response
		return response()->json($response);
	}

    //yang terbaru with_voucher
    public function generate_payment_url_renewal(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());
        $pl_id = $request->pl_id;
        $bil_id = $request->bil_id;
        $wil_id = $request->wil_id;
        $warga_id = $request->warga_id;

        $paymentUrl = '';

        $order_no = 'ES'.Carbon::now()->timestamp;
        //Generate Order
        GenerateSubscribeOrder::create([
            'order_no' => $order_no,
            'pl_id' => $pl_id,
            'wil_id' => $wil_id,
            'bil_id' => $bil_id,
            'warga_id' => $warga_id,
            'created_date' => Carbon::now()
        ]);

        $billing = Billing::find($bil_id);

        $paketLangganan = PaketLangganan::find($pl_id);
        $warga = Warga::find($warga_id);
        $wilayah = Wilayah::find($wil_id);

        $paymentUrl = Payment::getPaymentUrl($order_no, $billing->bil_jumlah, $wilayah->wil_nama.' ('.'Paket '.$paketLangganan->pl_nama.')', $warga->warga_email);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['paymentUrl'] = $paymentUrl;

		// return json response
		return response()->json($response);
	}

    public function kirim_email_pdf(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $data = array();
        $warga_id = $request->warga_id;

        $billing = new Billing;
        $warga = new Warga;

        $itemWarga = $warga->get_detail($warga_id);
        $itemBilling = $billing->getRecentBilling($itemWarga->wil_id);

        if(empty($itemBilling)) {
            $response['status'] = 'failed';
            $response['message'] = 'Tidak ada data tagihan';
            return response()->json($response);
        }

        $dataWilayah = Wilayah::find($itemWarga->wil_id);

        $kelurahan = Kel::find($dataWilayah->kel_id);
        $kecamatan = Kec::find($kelurahan->kec_id);
        $kabkota = Kab::find($kecamatan->kabkota_id);

        $to_name = $itemWarga->warga_nama_depan;
        $to_email = $itemWarga->user_email;

        $data['penanggung_jawab'] = $itemWarga->warga_nama_depan." ".$itemWarga->warga_nama_belakang;
        $data['wil_nama'] = $itemWarga->wil_nama;
        $data['wil_alamat'] = $itemWarga->wil_alamat;
        $data['kabkota_nama'] = $kabkota->kabkota_nama;

        $data['nama_paket'] = $itemBilling->pl_nama;
        $data['nomor_tagihan'] = $itemBilling->bil_no;
        $data['periode_dari'] = Carbon::parse($itemBilling->bil_mulai)->isoFormat('D MMMM Y');
        $data['periode_sampai'] = Carbon::parse($itemBilling->bil_akhir)->isoFormat('D MMMM Y');
        $data['periode_tagihan'] = $data['periode_dari']." - ".$data['periode_sampai'];
        $data['tgl_tagihan'] = Carbon::parse($itemBilling->bil_date)->isoFormat('D MMMM Y');
        $data['tgl_jatuh_tempo'] = Carbon::parse($itemBilling->bil_due)->isoFormat('D MMMM Y');
        $data['jumlah_tagihan'] = "Rp.".number_format($itemBilling->bil_jumlah, 0, ',', '.');

        $pdf = PDF::loadView('emails.billingpdf', $data)->setPaper('a4', 'portrait');

        Mail::send('emails.billingpdf_cop', $data, function($message) use ($to_name, $to_email, $pdf, $data) {
            $message->to($to_email, $to_name)
                    ->subject('Tagihan Aplikasi Rukun '.$data['wil_nama'])
                    ->from('rukun.id.99@gmail.com','Rukun')
                    ->attachData($pdf->output(), $data['nomor_tagihan'].".pdf");
        });

        // response
		$response['status'] = "success";
		$response['message'] = "OK";

        // return json response
		return response()->json($response);
    }

    //buat CRM
	public function list_crm($wil_id, Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $length = $request->get("length");
        $search = $request->get('search')['value'];

        $res = DB::table('billing as b')
					->join('paket_langganan as pl','b.pl_id','=','pl.pl_id')
					->join('wilayah as w', 'w.wil_id','=','b.wil_id')
					->join('marketing as ma', 'ma.wil_id','=','w.wil_id', 'left')
					->join('sales as sa', 'ma.sales_id','=','sa.sales_id', 'left')
					->select('w.wil_status', 'pl.pl_nama', 'w.wil_expire', 'w.wil_retensi_trial', 'sa.sales_nama')
					->where('b.wil_id',$wil_id)
                    ->orderBy('b.bil_id', 'desc')
                    ->limit(1);
        $res = $res->get();
        $data = array();
		if(!empty($res) || $res !=''){

			foreach($res as $r) {
                $status = array( 'Masa Trial', 'Masa Retensi Trial', 'Berhenti (dari Trial)', 'Berlangganan', 'Masa Retensi Berlangganan', 'Berhenti Berlangganan');

				$data[] = array(
					$status[$r->wil_status-1],
					$r->pl_nama,
					(Carbon::parse($r->wil_expire)->format('d-m-Y') ),
					(Carbon::parse($r->wil_retensi_trial)->format('d-m-Y') ),
					$r->sales_nama,
				);
			}

			$output = array(
				"draw" => 1,
				"recordsTotal" => 1,
				"recordsFiltered" => 1,
				"data" => $data
			);
		}else{
			$output = array(
				"draw" => $draw,
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => $data
			);
		}

        /*if(empty($info) || $info ='')
        {
            $response['status'] = "error";
            $response['message'] = "Wilayah not found";
            return response()->json($response);
            exit();
        }*/


        /*$result = Wilayah::where('wilayah.wil_head', '1')->latest()->get();
         $response['status'] = "success";
         $response['message'] = "OK";
         $response['results'] = $result;*/

         // return json response
         return response()->json($output, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

    }
    //riwayat berlangganan
	public function riwayat_crm($wil_id, Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $length = $request->get("length");
        $search = $request->get('search')['value'];

        $order =  $request->get('order');

         $col = 0;
         $dir = "";

         if(!empty($order)) {
             foreach($order as $o) {
                 $col = $o['column'];
                 $dir= $o['dir'];
             }
        }

         if($dir != "asc" && $dir != "desc") {
             $dir = "asc";
         }
         $columns_valid = array("bil_mulai", "bil_akhir", "bil_date");
         if(!isset($columns_valid[$col])) {
            $order = 'null';
        } else {
            $order = $columns_valid[$col];
        }

        $res = DB::table('billing as b')
					->join('paket_langganan as pl','b.pl_id','=','pl.pl_id')
					->join('wilayah as w', 'w.wil_id','=','b.wil_id')
					->join('marketing as ma', 'ma.wil_id','=','w.wil_id', 'left')
					->join('sales as sa', 'ma.sales_id','=','sa.sales_id', 'left')
					->select('b.bil_mulai', 'b.bil_akhir', 'w.wil_status', 'pl.pl_nama', 'b.bil_no', 'b.bil_date', 'b.bil_status', 'b.bil_id')
					->where('b.wil_id',$wil_id)
                    ->orderBy('b.bil_id', 'desc');

		/*if($search!=''){
			$res = $res->where('a.wil_nama','ilike',"%$search%");
		}*/

        if($request->get('bil_status')){
            $res = $res->where('b.bil_status', $request->bil_status);
        }
		if(isset($order)){
				$res = $res->orderBy($order);
		}else{
				$order = $res->orderBy('b.bil_id');
		}
		if(isset($length) || isset($start)){
				$res = $res->skip($start)->take($length);
		}
        $res = $res->get();
        $data = array();
        $i = 1;
		if(!empty($res) || $res !=''){

			foreach($res as $r) {
				$status = array( 'Masa Trial', 'Masa Retensi Trial', 'Berhenti (dari Trial)', 'Berlangganan', 'Masa Retensi Berlangganan', 'Berhenti Berlangganan');
                if($r->bil_status == 1){
                    $bil_status = 'Sudah Dibayar';
                }else{
                    $bil_status = 'Belum Dibayar';
                }
                if(!$r->bil_mulai || $r->bil_mulai == NULL || $r->bil_mulai == ''){
                    $bil_mulai = '';
                }else{
                    $bil_mulai = Carbon::parse($r->bil_mulai)->format('d M Y');
                }
                if(!$r->bil_akhir || $r->bil_akhir == NULL || $r->bil_akhir == ''){
                    $bil_akhir = '';
                }else{
                    $bil_akhir = Carbon::parse($r->bil_akhir)->format('d M Y');
                }
				$data[] = array(
					$start + $i,
                    $bil_mulai,
                    $bil_akhir,
					$status[$r->wil_status-1],
                    $r->pl_nama,
                    $r->bil_no,
					(Carbon::parse($r->bil_date)->format('d M Y') ),
					$bil_status,
					'<a href="#detil-inv" class="btn btn-primary" title="Detail" onclick="showDet('.$r->bil_id.')" id="detil-btn" data-idb="'.$r->bil_id.'"><i class="fa fa-info text-light" ></i></a>'
				);
                $i++;
			}
            //total data lead
            $total_sal = DB::table('billing as b')
                            ->where('b.wil_id',$wil_id)->get()->count();
            //total filtered
            $total_fil = DB::table('billing as b')
                            ->where('b.wil_id',$wil_id);
            if($request->get('bil_status')){
                $total_fil = $total_fil->where('b.bil_status', $request->bil_status);
            }
            $total_fil = $total_fil->get()->count();

			$output = array(
				"draw" => $draw,
				"recordsTotal" => $total_sal,
				"recordsFiltered" => $total_fil,
				"data" => $data
			);
		}else{
			$output = array(
				"draw" => $draw,
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => $data
			);
		}

         // return json response
         return response()->json($output, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

    }
    //detil invoice
    public function detail_inv($bil_id){
			$info =DB::table('billing as b')
			->join('paket_langganan as c','c.pl_id','=','b.pl_id')
			->where('b.bil_id',$bil_id)
			->select('b.*', 'c.pl_nama')
			->first();
			//return $info;
			return response()->json($info);
    }
    /*== Add ==*/
	public function add(Request $request)
	{
        try{

            $billing = new Billing;
            $billing->bil_no = $request->bil_no;
            $billing->bil_date = $request->bil_date;
            $billing->bil_mulai = $request->bil_mulai;
            $billing->bil_akhir = $request->bil_akhir;
            $billing->bil_due = $request->bil_due;
            $billing->bil_jumlah = str_replace(",", ".", str_replace(".", "", $request->bil_jumlah));
            $billing->pl_id = $request->pl_id;
            $billing->wil_id = $request->wil_id;
            $billing->wil_nama = $request->wil_nama;

            $billing->save();

            // response
            $response['status'] = "success";
            $response['message'] = "OK";
            $response['results'] = "Sukses menyimpan invoice baru";
            // return json response
            return response()->json($response);

         }
         catch(\Exception $e){
             // response
             $response['status'] = "error";
             $response['message'] = "error";
             $response['results'] =  $e->getMessage();
             // return json response
             return response()->json($response);
         }

         if($this->notif_mobile($billing->bil_id)){
             return "sukses fcm";
         }else{
             return "gagal fcm";
         }


	}
    /*== Bayar Invoice ==*/
	public function bayar(Request $request)
	{
        try{

            $billing = new Billing;
            $bil_id = $request->bil_id;
            $billing = Billing::find($bil_id);

            $billing->bil_tgl_bayar = $request->bil_tgl_bayar;
            $billing->bil_cara_bayar = $request->bil_cara_bayar;
            $billing->bil_jml_bayar = str_replace(",", ".", str_replace(".", "", $request->bil_jml_bayar));
            $bil_bukti = $request->file('bil_bukti');

            // upload img
            if($bil_bukti!='')
            {
                // destination path
                $destination_path = public_path('img/bukti_bayar/');
                $img = $bil_bukti;

                // upload
                $md5_name = uniqid()."_".md5_file($img->getRealPath());
                $ext = $img->getClientOriginalExtension();
                $img->move($destination_path,"$md5_name.$ext");
                $img_file = "$md5_name.$ext";

                // resize photo
                $img = Image::make(URL("public/img/bukti_bayar/$md5_name.$ext"));
                $img->fit(500);
                $img->save(public_path("img/bukti_bayar/$md5_name.$ext"));

                // set data
                $billing->bil_bukti = $img_file;

            }

            $billing->save();

            // response
            $response['status'] = "success";
            $response['message'] = "OK";
            $response['results'] = "Sukses mengubah invoice";
            // return json response
            return response()->json($response);
        }
         catch(\Exception $e){
             // response
             $response['status'] = "error";
             $response['message'] = "error";
             $response['results'] =  $e->getMessage();
             // return json response
             return response()->json($response);
        }


	}

    public function notif_mobile($bil_id)
    {

                    //------------------------------------------------------------------
                    // echo "hari ini sudah memasuki h -5 masa berlangganan, kirim notifikasi";
                    // echo $wil_id;
                    $pengurus = DB::table('pengurus as a')
                    ->join('warga as b','b.warga_id','=','a.warga_id')
                    ->join('masa_kepengurusan as c','c.mk_id','=','a.mk_id')
                    ->join('core_user as d','d.user_ref_id','=','a.warga_id')
                    ->join('billing as bil', 'bil.wil_id','=','b.wil_id')
                    ->select('d.fcm_token', 'b.warga_nama_depan', 'b.warga_id', 'bil.bil_no')
                    ->where('b.bil_id',$bil_id)
                    ->get();

                    // Update status expired = 6
                    //

                    // $wilayah = Warga::find($wil_id);
                    // $wilayah->wil_status = 6;
                    // $wilayah->save();

                    //send to user peegurus
                    $endpoint = "https://fcm.googleapis.com/fcm/send";
                    $client = new \GuzzleHttp\Client();
                    //

                    foreach ($pengurus as $rows) {
                        $fcm_token = $rows->fcm_token;
                        $warga_nama = $rows->warga_nama_depan;
                        $warga_id =  $rows->warga_id;
                        $bil_no =  $rows->bil_no;

                        //
                        $title = 'Notifikasi Tagihan Terbaru.';
                        $ket = 'Tagihan Aplikasi Rukun dengan no.'.$bil_no.' sudah tersedia. Harap segera lakukan pembayaran.';
                        $body = substr(strip_tags($ket),0,100)."...";

                        //create json data
                        $data_json = [
                                'notification' => [
                                    'title' => 'Halo '.$warga_nama.'',
                                    'body' => $ket,
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    'sound' => 'alarm.mp3'
                                ],
                                'data' => [
                                    'id' => ''.$bil_id.'',
                                    'page' => 'billing'
                                ],
                                'to' => ''.$fcm_token.''
                            ];

                        $requestAPI = $client->post( $endpoint, [
                            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                            'body' => json_encode($data_json)
                        ]);

                        Notifikasi::create([
                            'warga_id' => $warga_id,
                            'notif_title' => substr($title,0,100),
                            'notif_body' => $body,
                            'notif_page' => 'billing',
                            'page_id' => $bil_id,
                            'page_sts' => 'new_billing',
                            'notif_date' => Carbon::now()
                        ]);
                    }

    }



}
