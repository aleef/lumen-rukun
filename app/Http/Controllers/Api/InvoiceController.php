<?php
namespace App\Http\Controllers\Api;

use App\BBDetil;
use App\BuktiBayar;
use App\GenerateOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Invoice;
use App\Warga;
use App\Periodetagihan;
use App\Tarif;
use Carbon\Carbon;
use App\Keuangan;
use App\Notifikasi;
use App\Payment;
use App\User;
use App\Wilayah;
use Mail;
use PDF;


class InvoiceController extends Controller
{

    public function list(Request $request, Warga $warga) {

        $response = array('status' => 'failed', 'is_matched' => '', 'message' => 'request failed', 'results' => array());

        $pt_id = $request->pt_id;
        $keyword = $request->keyword;

        $periodeTagihan = new Periodetagihan;
        $invoice = new Invoice;

        $itemPeriode = $periodeTagihan->getItem($pt_id);
        if(empty($itemPeriode)) {
            $response['message'] = 'ID tidak ditemukan';
            return response()->json($response);
        }

        $wargaList = $warga->get_list($itemPeriode->wil_id, $keyword);
        if(empty($wargaList)) {
            $response['message'] = 'Warga tidak ditemukan';
            return response()->json($response);
        }

        $tagihanList = array();


        $i = 0;
        $itemTagihan = array();
        $tarif = new Tarif;


        //periode sebelumnya
        $tahun_sebelumnya = $itemPeriode->pt_tahun;
        if($itemPeriode->pt_bulan == 1) {
            $bulan_sebelumnya = 12;
            $tahun_sebelumnya = $tahun_sebelumnya - 1;
        }else {
            $bulan_sebelumnya = $itemPeriode->pt_bulan - 1;
        }

        $previousPeriode = Periodetagihan::where('wil_id',$itemPeriode->wil_id)
                            ->where('pt_tahun',$tahun_sebelumnya)
                            ->where('pt_bulan',$bulan_sebelumnya)
                            ->first();


        foreach($wargaList as $item) {
            $itemTagihan = $invoice->getItem($pt_id, $item->warga_id);
            if(empty($itemTagihan)) {


                if(empty($previousPeriode)) {
                    $lastTagihan = Invoice::where('warga_id', $item->warga_id)
                                        ->orderBy('pt_id','desc')
                                        ->first();

                }else {
                    $lastTagihan = Invoice::where('warga_id', $item->warga_id)
                                        ->where('pt_id', $previousPeriode->pt_id)
                                        ->first();

                }

                $last_listrik_akhir = 0;
                $last_air_akhir = 0;
                if(!empty($lastTagihan)) {
                    $last_listrik_akhir = $lastTagihan->tag_listrik_akhir;
                    $last_air_akhir = $lastTagihan->tag_air_akhir;
                }

                $kategoriBangunan = $invoice->getKategoriBangunan($item->kb_id);
                $tarif_ipl = $kategoriBangunan->kb_tarif_ipl;

                $tagihanList[$i]['tag_id'] = null;
                $tagihanList[$i]['warga_id'] = (int) $item->warga_id;
                $tagihanList[$i]['pt_id'] = (int) $pt_id;
                $tagihanList[$i]['tag_ipl'] = (double) $tarif_ipl;
                $tagihanList[$i]['kb_keterangan'] = $kategoriBangunan->kb_keterangan;

                $tagihanList[$i]['tag_listrik_abo'] = (double) $tarif->getNilai('ABODEMEN_LISTRIK', $itemPeriode->wil_id);
                $tagihanList[$i]['tag_listrik_awal'] = (double) $last_listrik_akhir;
                $tagihanList[$i]['tag_listrik_akhir'] = (double) 0;
                $tagihanList[$i]['tag_listrik_per_kwh'] = (double) $tarif->getNilai('TARIF_LISTRIK_PER_KWH', $itemPeriode->wil_id);
                $tagihanList[$i]['tag_listrik_total'] = (($tagihanList[$i]['tag_listrik_akhir'] - $tagihanList[$i]['tag_listrik_awal']) * $tagihanList[$i]['tag_listrik_per_kwh']) + $tagihanList[$i]['tag_listrik_abo'];

                $tagihanList[$i]['tag_air_abo'] = (double) $tarif->getNilai('ABODEMEN_AIR', $itemPeriode->wil_id);
                $tagihanList[$i]['tag_air_awal'] = (double) $last_air_akhir;
                $tagihanList[$i]['tag_air_akhir'] = (double) 0;
                //$tagihanList[$i]['tag_air_total'] = (double) 35000;
                $tagihanList[$i]['tag_air_per_m3'] = (double) $tarif->getNilai('TARIF_AIR_PER_M3', $itemPeriode->wil_id);
                $tagihanList[$i]['tag_air_total'] = (($tagihanList[$i]['tag_air_akhir'] - $tagihanList[$i]['tag_air_awal']) * $tagihanList[$i]['tag_air_per_m3']) + $tagihanList[$i]['tag_air_abo'];

                $tagihanList[$i]['tag_lain'] = (double) 0;
                $tagihanList[$i]['tag_denda'] = (double) 0;
                $tagihanList[$i]['tag_total'] = (double) ($tarif_ipl + $tagihanList[$i]['tag_listrik_total'] + $tagihanList[$i]['tag_air_total']);
                $tagihanList[$i]['warga_nama'] = $item->warga_nama_depan.' '.$item->warga_nama_belakang;
                $tagihanList[$i]['warga_hp'] = $item->warga_hp;
                $tagihanList[$i]['warga_alamat'] = $item->warga_alamat;
                $tagihanList[$i]['warga_no_rumah'] = $item->warga_no_rumah;

            }else {
                $tagihanList[$i]['tag_id'] = (int) $itemTagihan->tag_id;
                $tagihanList[$i]['warga_id'] = (int) $itemTagihan->warga_id;
                $tagihanList[$i]['pt_id'] = (int) $itemTagihan->pt_id;
                $tagihanList[$i]['tag_ipl'] = (double) $itemTagihan->tag_ipl;
                $tagihanList[$i]['kb_keterangan'] = '';

                $tagihanList[$i]['tag_listrik_abo'] = (double) $itemTagihan->tag_listrik_abo;
                $tagihanList[$i]['tag_listrik_awal'] = (double) $itemTagihan->tag_listrik_awal;
                $tagihanList[$i]['tag_listrik_akhir'] = (double) $itemTagihan->tag_listrik_akhir;
                $tagihanList[$i]['tag_listrik_total'] = (double) $itemTagihan->tag_listrik_total;
                $tagihanList[$i]['tag_listrik_per_kwh'] = (double) $itemTagihan->tag_listrik_per_kwh;

                $tagihanList[$i]['tag_air_abo'] = (double) $itemTagihan->tag_air_abo;
                $tagihanList[$i]['tag_air_awal'] = (double) $itemTagihan->tag_air_awal;
                $tagihanList[$i]['tag_air_akhir'] = (double) $itemTagihan->tag_air_akhir;
                $tagihanList[$i]['tag_air_total'] = (double) $itemTagihan->tag_air_total;
                $tagihanList[$i]['tag_air_per_m3'] = (double) $itemTagihan->tag_air_per_m3;

                $tagihanList[$i]['tag_lain'] = (double) $itemTagihan->tag_lain;
                $tagihanList[$i]['tag_denda'] = (double) $itemTagihan->tag_denda;
                $tagihanList[$i]['tag_total'] = (double) $itemTagihan->tag_total;
                $tagihanList[$i]['warga_nama'] = $item->warga_nama_depan.' '.$item->warga_nama_belakang;
                $tagihanList[$i]['warga_hp'] = $item->warga_hp;
                $tagihanList[$i]['warga_alamat'] = $item->warga_alamat;
                $tagihanList[$i]['warga_no_rumah'] = $item->warga_no_rumah;

            }
            $i++;
        }

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tagihanList;
        $response['is_matched'] = $invoice->isMatch($itemPeriode->wil_id, $pt_id);
        $response['is_sent'] = $itemPeriode->pt_status;


		// return json response
		return response()->json($response);
    }


    public function list_pembayaran(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $pt_id = $request->pt_id;
        $status = $request->status;

        $invoice = new Invoice;

        $tagihanList = array();
        $tagihanList = $invoice->getListPembayaran($pt_id, $status);
        $totalAll = $invoice->totalAll($pt_id);
                // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tagihanList;
        $response['total'] = $totalAll;

		// return json response
		return response()->json($response);
    }

    //list warga
    public function list_tunggakan_warga(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;

        $invoice = new Invoice;

        $tunggakanList = array();
        $tunggakanList = $invoice->getListTunggakanWarga($wil_id);

                // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tunggakanList;

		// return json response
		return response()->json($response);
    }

    //list periode tunggakan
    public function list_tunggakan_periode_warga(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;

        $invoice = new Invoice;

        $tunggakanList = array();
        $tunggakanList = $invoice->getListPeriodeTunggakanWarga($warga_id);

        $textPeriode = $invoice->getInfoPeriodeTunggakan($warga_id);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tunggakanList;
        $response['total_amount'] = $invoice->getAmountTotalTunggakanWarga($warga_id);
        $response['periode_tunggakan'] = $textPeriode;

		// return json response
		return response()->json($response);
    }

    //list tunggakan warga per periode (e.g Januari 2021)
    public function list_tunggakan_warga_per_periode(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $pt_id = $request->pt_id;
        $invoice = new Invoice;

        $tunggakanList = array();
        $tunggakanList = $invoice->getListTunggakanWargaPerPeriode($pt_id);

                // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tunggakanList;

        if(count($tunggakanList) == 0) {
            $response['total_warga'] = 0;
            $response['total_amount'] = 0;
        }else {
            $total_amount = 0;
            foreach($tunggakanList as $tunggakan) {
                $total_amount += (double) $tunggakan->tag_total;
            }

            $response['total_warga'] = count($tunggakanList);
            $response['total_amount'] = $total_amount;
        }
		// return json response
		return response()->json($response);
    }


    public function add(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = (int) $request->warga_id;

        //cek subscription
        $wargaItem = Warga::find($warga_id);
        $wil_id = $wargaItem->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $pt_id = (int) $request->pt_id;
        $tag_ipl = (double) $request->tag_ipl;

        $tag_listrik_abo = (double) $request->tag_listrik_abo;
        $tag_listrik_awal = (double) $request->tag_listrik_awal;
        $tag_listrik_akhir =  (double) $request->tag_listrik_akhir;
        $tag_listrik_total = (double) $request->tag_listrik_total;
        $tag_listrik_per_kwh = (double) $request->tag_listrik_per_kwh;

        $tag_air_abo = (double) $request->tag_air_abo;
        $tag_air_awal = (double) $request->tag_air_awal;
        $tag_air_akhir = (double) $request->tag_air_akhir;
        $tag_air_total = (double) $request->tag_air_total;
        $tag_air_per_m3 = (double) $request->tag_air_per_m3;


        $tag_lain = (double) $request->tag_lain;
        $tag_denda = (double) $request->tag_denda;
        $tag_total = (double) $request->tag_total;

        $invoice = new Invoice;
        $itemTagihan = $invoice->getItem($pt_id, $warga_id);
        if(!empty($itemTagihan)) {
            $response['status'] = "success";
		    $response['message'] = "Data duplicate";
		    $response['results'] = array('tag_id' => $itemTagihan->tag_id);

            // return json response
            return response()->json($response);
        }

        //5000 harusnya ambil dari table
        $tag_listrik_total = ($tag_listrik_per_kwh * ($tag_listrik_akhir - $tag_listrik_awal)) + $tag_listrik_abo;

        //10000 harusnya ambil dari table
        $tag_air_total = ($tag_air_per_m3 * ($tag_air_akhir - $tag_air_awal)) + $tag_air_abo;
        $tag_total = $tag_ipl + $tag_listrik_total + $tag_air_total + $tag_lain + $tag_denda;

        $order_no = 'R2'.Carbon::now()->timestamp;

        $invoice->pt_id = $pt_id;
        $invoice->warga_id = $warga_id;
        $invoice->tag_ipl = $tag_ipl;
        $invoice->tag_listrik_abo = $tag_listrik_abo;
        $invoice->tag_listrik_awal = $tag_listrik_awal;
        $invoice->tag_listrik_akhir = $tag_listrik_akhir;
        $invoice->tag_listrik_total = $tag_listrik_total;
        $invoice->tag_listrik_per_kwh = $tag_listrik_per_kwh;
        $invoice->tag_air_abo = $tag_air_abo;
        $invoice->tag_air_awal = $tag_air_awal;
        $invoice->tag_air_akhir = $tag_air_akhir;
        $invoice->tag_air_total = $tag_air_total;
        $invoice->tag_air_per_m3 = $tag_air_per_m3;
        $invoice->tag_lain = $tag_lain;
        $invoice->tag_denda = $tag_denda;
        $invoice->tag_total = $tag_total;
        $invoice->order_no = $order_no;
        $invoice->created_date = Carbon::now();
        $invoice->updated_date = Carbon::now();
        $invoice->save();

        //Generate Order
        GenerateOrder::create([
            'order_no' => $order_no,
            'tag_ids' => $invoice->tag_id
        ]);


        //Kondisi jika warga baru daftar belakangan, dan pengurus telah mengirim tagihan periode X ke seluruh warga
        //berarti harus cek dulu di periode_tagihan ketika pt_status = 'S', maka langsung kirim notifikasi ke warga yg baru diinput tagihannya

        $periodeTagihan = Periodetagihan::find($pt_id);
        if($periodeTagihan->pt_status == 'S') { //status sudah terkirim
            $monthNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

            $userItem = User::where('user_ref_id',$warga_id)->first();
            $wargaItem = Warga::find($warga_id);

             //send to user peegurus
            $endpoint = "https://fcm.googleapis.com/fcm/send";
            $client = new \GuzzleHttp\Client();

            $fcm_token = $userItem->fcm_token;
            $title = 'Halo, '.$wargaItem->warga_nama_depan;
            $body = 'Ini tagihan Anda Bulan '.$monthNames[$periodeTagihan->pt_bulan].' '.$periodeTagihan->pt_tahun;


            Notifikasi::create([
                'warga_id' => $warga_id,
                'notif_title' => substr($title,0,100),
                'notif_body' => substr($body,0,255),
                'notif_page' => 'tagihan_belum_lunas',
                'page_id' => $invoice->tag_id,
                'page_sts' => null,
                'notif_date' => Carbon::now()
            ]);

            //create json data
            $data_json = [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound'	=> 'alarm.mp3'
                    ],
                    'data' => [
                        'id' => $invoice->tag_id,
                        'panic_tgl' => '',
                        'panic_jam' => '',
                        'panic_sts' => '',
                        'page' => 'tagihan_belum_lunas'
                    ],
                    'to' => ''.$fcm_token.'',
                    'collapse_key' => 'type_a',
                ];

            $requestAPI = $client->post( $endpoint, [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                'body' => json_encode($data_json)
            ]);
        }

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = array('tag_id' => $invoice->tag_id, 'pt_id' => $pt_id);

		// return json response
		return response()->json($response);
    }


    public function update(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $tag_id = (int) $request->tag_id;
        $warga_id = (int) $request->warga_id;

        //cek subscription
        $wargaItem = Warga::find($warga_id);
        $wil_id = $wargaItem->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $pt_id = (int) $request->pt_id;

        $tag_ipl = (double) $request->tag_ipl;
        $tag_listrik_abo = (double) $request->tag_listrik_abo;
        $tag_listrik_awal = (double) $request->tag_listrik_awal;
        $tag_listrik_akhir = (double) $request->tag_listrik_akhir;
        $tag_listrik_total = (double) $request->tag_listrik_total;
        $tag_listrik_per_kwh = (double) $request->tag_listrik_per_kwh;

        $tag_air_abo = (double) $request->tag_air_abo;
        $tag_air_awal = (double) $request->tag_air_awal;
        $tag_air_akhir = (double) $request->tag_air_akhir;
        $tag_air_total = (double) $request->tag_air_total;
        $tag_air_per_m3 = (double) $request->tag_air_per_m3;

        $tag_lain = (double) $request->tag_lain;
        $tag_denda = (double) $request->tag_denda;
        $tag_total = (double) $request->tag_total;

        //5000 harusnya ambil dari table
        $tag_listrik_total = ($tag_listrik_per_kwh * ($tag_listrik_akhir - $tag_listrik_awal)) + $tag_listrik_abo;

        //10000 harusnya ambil dari table
        $tag_air_total = ($tag_air_per_m3 * ($tag_air_akhir - $tag_air_awal)) + $tag_air_abo;
        $tag_total = $tag_ipl + $tag_listrik_total + $tag_air_total + $tag_lain + $tag_denda;

        try {
            $invoice = Invoice::findOrFail($tag_id);
        }catch(Exception $e) {
            $response['message'] = 'Update gagal. Data tidak ada';
            return response()->json($response);
        }

        if(!empty($invoice->tag_tgl_bayar) or $invoice->tag_status == '1') { //sudah bayar tidak dapat di update
            $response['message'] = 'Update gagal. Tagihan sudah dibayar';
            return response()->json($response);
        }

        if(empty($invoice->tag_tgl_bayar) and $invoice->tag_status == '2') { //menunggu validasi
            $response['message'] = 'Update gagal. Tagihan sedang dalam validasi';
            return response()->json($response);
        }

        if(!empty($invoice->payment_url)
            and ((int)$tag_total != (int)$invoice->tag_total)) {
            //Update Payment Url kalau sudah ada
            $order_no = 'R2'.Carbon::now()->timestamp;
            //Generate Order Baru
            GenerateOrder::create([
                'order_no' => $order_no,
                'tag_ids' => $tag_id
            ]);

            $warga = new Warga;
            $itemWarga = $warga->get_detail($warga_id);

            $paymentUrl = Payment::getPaymentUrl($order_no, $tag_total, $itemWarga->warga_nama_depan, $itemWarga->user_email);

            $invoice->order_no = $order_no;
            $invoice->payment_url = $paymentUrl;
            $invoice->payment_url_created_date = Carbon::now();
        }

        $invoice->tag_id = $tag_id;
        $invoice->pt_id = $pt_id;
        $invoice->warga_id = $warga_id;
        $invoice->tag_ipl = $tag_ipl;
        $invoice->tag_listrik_abo = $tag_listrik_abo;
        $invoice->tag_listrik_awal = $tag_listrik_awal;
        $invoice->tag_listrik_akhir = $tag_listrik_akhir;
        $invoice->tag_listrik_total = $tag_listrik_total;
        $invoice->tag_listrik_per_kwh = $tag_listrik_per_kwh;
        $invoice->tag_air_abo = $tag_air_abo;
        $invoice->tag_air_awal = $tag_air_awal;
        $invoice->tag_air_akhir = $tag_air_akhir;
        $invoice->tag_air_total = $tag_air_total;
        $invoice->tag_air_per_m3 = $tag_air_per_m3;
        $invoice->tag_lain = $tag_lain;
        $invoice->tag_denda = $tag_denda;
        $invoice->tag_total = $tag_total;
        $invoice->updated_date = Carbon::now();

        //do update
        $invoice->save();

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = array('tag_id' => $invoice->tag_id, 'pt_id' => $pt_id);

		// return json response
		return response()->json($response);
    }

    public function delete(Request $request)
	{
		$tag_id = $request->tag_id;
		$response = array('status' => 'failed', 'message' => "Error, can't delete tagihan");

        try {
			$invoice = Invoice::findOrFail($tag_id);
            $periodeTagihan = Periodetagihan::find($invoice->pt_id);

            //cek subscription
            $wargaItem = Warga::find($invoice->warga_id);
            $wil_id = $wargaItem->wil_id;
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }

            if($periodeTagihan->pt_status == 'S') {
                $response['message'] = 'Delete gagal. Tagihan sudah dikirim ke warga';
                return response()->json($response);
            }

            if(!empty($invoice->tag_tgl_bayar) or $invoice->tag_status == '1') { //sudah bayar tidak dapat di update
                $response['message'] = 'Delete gagal. Tagihan sudah dibayar';
                return response()->json($response);
            }

            $invoice->delete();
		}
		catch(\Exception $e) {
            $response['message'] = 'Delete gagal. Data tidak ada';
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}

    public function cek_status(Request $request)
	{
		$tag_id = $request->tag_id;
		$response = array('status' => 'failed', 'message' => "Error, status tagihan gagal diambil", 'results' => array());

        try {
			$invoice = Invoice::findOrFail($tag_id);

            // response
            $response['status'] = "success";
            $response['message'] = "OK";
            $response['results'] = $invoice;

            // return json response
            return response()->json($response);
		}
		catch(\Exception $e) {
            $response['message'] = 'Cek status gagal. Data tidak ada';
			return response()->json($response);
			exit();
		}

	}

    public function kirimTagihan(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed');

        $pt_id = $request->pt_id;
        $wil_id = $request->wil_id;


        $invoice = new Invoice;

        if(!empty($wil_id)) {
            $is_match = $invoice->isMatch($wil_id, $pt_id);
            if($is_match == 'T') {
                $response['message'] = 'Jumlah input != jumlah warga';
                return response()->json($response);
            }
        }


        $listWarga = $invoice->getListTagihanWithToken($pt_id);
        $wilayah = Wilayah::find($wil_id);
        $wil_tag_due = $wilayah->wil_tag_due;
        $daysInMonth = Carbon::now()->daysInMonth;
        $dueDate = ($wil_tag_due > $daysInMonth ) ? $daysInMonth : $wil_tag_due;

        if(!empty($listWarga)) {

            //update periode tagihan dengan status 'S' (Sent)
            $periodeTagihan = Periodetagihan::find($pt_id);
            $periodeTagihan->pt_id = $pt_id;
            $periodeTagihan->pt_status = 'S';
            $periodeTagihan->save();

            //send to user peegurus
			$endpoint = "https://fcm.googleapis.com/fcm/send";
			$client = new \GuzzleHttp\Client();

			foreach ($listWarga as $item) {

                $tagihan = Invoice::find($item->tag_id);
                $tagihan->tag_id = $item->tag_id;
                $tagihan->send_date = Carbon::now();
                $tagihan->tag_no = Invoice::generateInvoiceNo($wil_id, $item->warga_id);
                $tagihan->tag_due = Carbon::parse($periodeTagihan->pt_tahun.'-'.$periodeTagihan->pt_bulan.'-'.$dueDate);

                $tagihan->save();


				$fcm_token = $item->fcm_token;
                $title = 'Halo, '.$item->warga_nama_depan;
                $body = 'Ini tagihan Anda Bulan '.$item->month_name.' '.$item->pt_tahun;


                Notifikasi::create([
                    'warga_id' => $item->warga_id,
                    'notif_title' => substr($title,0,100),
                    'notif_body' => substr($body,0,255),
                    'notif_page' => 'tagihan_belum_lunas',
                    'page_id' => $item->tag_id,
                    'page_sts' => null,
                    'notif_date' => Carbon::now()
                ]);

				//create json data
				$data_json = [
				        'notification' => [
				        	'title' => $title,
				        	'body' => $body,
				        	'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
				        	'sound'	=> 'alarm.mp3'
				        ],
						'data' => [
				        	'id' => $item->tag_id,
				        	'panic_tgl' => '',
				        	'panic_jam' => '',
				        	'panic_sts' => '',
				        	'page' => 'tagihan_belum_lunas'
				        ],
				        'to' => ''.$fcm_token.'',
						'collapse_key' => 'type_a',
				    ];

				$requestAPI = $client->post( $endpoint, [
			        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
			        'body' => json_encode($data_json)
			    ]);

			}

            $response['status'] = 'success';
            $response['message'] = 'Tagihan terkirim ke '.count($listWarga). ' warga';
            $response['token'] = $fcm_token;
        }else {
            $response['status'] = 'success';
            $response['message'] = 'Tagihan terkirim ke 0 warga';
        }

        return response()->json($response);
    }

    public function detail(Request $request, Invoice $invoice)
	{

        $tag_id = $request->tag_id;
        $isGeneratedPaymentUrl = $request->generate_payment_url;

        // get data
		$dataInvoice = $invoice->getDetail($tag_id);
		if(empty($dataInvoice))
		{
			$response['status'] = "error";
			$response['message'] = "Informasi not found";
			return response()->json($response);
			exit();
		}

        $warga = new Warga;
        $itemWarga = $warga->get_detail($dataInvoice->warga_id);

        if(!empty($isGeneratedPaymentUrl) and empty($dataInvoice->payment_url)) {
            //Ambil halaman payment Midtrans
            $paymentUrl = Payment::getPaymentUrl($dataInvoice->order_no, (int)$dataInvoice->tag_total, $itemWarga->warga_nama_depan, $itemWarga->user_email);

            $inv = Invoice::find($tag_id);
            $inv->payment_url = $paymentUrl;
            $inv->payment_url_created_date = Carbon::now();
            $inv->save();

            $dataInvoice->payment_url = $paymentUrl;
        }elseif(!empty($isGeneratedPaymentUrl)) {

            if(empty($dataInvoice->tag_tgl_bayar)) { //hanya jika belum bayar
                //cek apakah link expired ( more than 24 hours )
                $startTime = Carbon::parse($dataInvoice->payment_url_created_date);
                $finishTime = Carbon::now();

                $diffHours = $finishTime->diffInHours($startTime);
                if($diffHours >= 24) { //24 hours or more create new payment url

                    $order_no = 'R2'.Carbon::now()->timestamp;
                    //Generate Order
                    GenerateOrder::create([
                        'order_no' => $order_no,
                        'tag_ids' => $dataInvoice->tag_id
                    ]);

                    $paymentUrl = Payment::getPaymentUrl($order_no, (int)$dataInvoice->tag_total, $itemWarga->warga_nama_depan, $itemWarga->user_email);

                    $inv = Invoice::find($tag_id);
                    $inv->payment_url = $paymentUrl;
                    $inv->payment_url_created_date = Carbon::now();
                    $inv->order_no = $order_no;
                    $inv->save();

                    $dataInvoice->payment_url = $paymentUrl;
                }
            }

        }

        $dataInvoice->warga_nama = $itemWarga->warga_nama_depan." ".$itemWarga->warga_nama_belakang;
        $dataInvoice->warga_alamat = $itemWarga->warga_alamat;
        $dataInvoice->warga_no_rumah = $itemWarga->warga_no_rumah;

        $itemPeriode = Periodetagihan::find($dataInvoice->pt_id);
        $dataInvoice->month_name = Periodetagihan::getMonthName($itemPeriode->pt_bulan);
        $dataInvoice->pt_tahun = $itemPeriode->pt_tahun;

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $dataInvoice;
        $response['total_tunggakan'] = $invoice->getAmountTotalTunggakanWarga($dataInvoice->warga_id);

		// return json response
		return response()->json($response);
	}

    //catat pembayaran manual oleh pengurus
    public function catat_pembayaran_manual(Request $request, Invoice $invoice)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $tag_id = $request->tag_id;
        $warga_id = $request->warga_id;
        $title_month = $request->title_month;
        $title_year = $request->title_year;
        $tag_total = $request->tag_total;
        $tag_jumlah_bayar = $request->tag_jumlah_bayar;
        $tgl_bayar = $request->tag_tgl_bayar;
        $tag_catatan_bayar = $request->tag_catatan_bayar;

        $tgl_bayar = strtotime($tgl_bayar);
        $tag_tgl_bayar = date('Y-m-d',$tgl_bayar);

        if($tag_total != $tag_jumlah_bayar) {
            $response['message'] = 'Total tagihan tidak sama dengan jumlah pembayaran';
            return response()->json($response);
			exit();
        }
		// get data
		$dataInvoice = Invoice::find($tag_id);
		if(empty($dataInvoice))
		{
			$response['message'] = "Data tagihan not found";
			return response()->json($response);
			exit();
		}

        if(!empty($dataInvoice->tag_tgl_bayar)) {
            $response['message'] = "Data tagihan sudah pernah dibayar";
			return response()->json($response);
        }


        $dataInvoice->tag_id = $tag_id;
        $dataInvoice->tag_total = $tag_total;
        $dataInvoice->tag_jumlah_bayar = $tag_jumlah_bayar;
        $dataInvoice->tag_catatan_bayar = $tag_catatan_bayar;
        $dataInvoice->tag_tgl_bayar = $tag_tgl_bayar;
        $dataInvoice->tag_cara_bayar = '1'; //pembayaran manual
        $dataInvoice->tag_status = '1'; //lunas
        $dataInvoice->save();

        $warga = new Warga;
        $itemWarga = $warga->get_detail($warga_id);

        $dataPeriodeTagihan = Periodetagihan::find($dataInvoice->pt_id);

        //pencatatan di table keuangan
        Keuangan::create([
            'tag_id' => $tag_id,
            'keu_tgl' => Carbon::now(),
            'keu_tgl_short' => date('Y-m-d'),
            'keu_status' => 1,
            'keu_sumbertujuan' => 'WARGA',
            'keu_deskripsi' => 'Pembayaran tagihan periode '.Periodetagihan::getMonthName($dataPeriodeTagihan->pt_bulan).'/'.$dataPeriodeTagihan->pt_tahun.' oleh : '.$itemWarga->warga_nama_depan.' '.$itemWarga->warga_nama_belakang,
            'keu_nominal' => $dataInvoice->tag_total,
            'wil_id' => $itemWarga->wil_id,
            'created_at' => Carbon::now()
        ]);

		// response
		$response['status'] = "success";
		$response['message'] = "Tagihan ".$title_month." ".$title_year. " atas nama ".$itemWarga->warga_nama_depan." telah lunas dibayar";
		$response['results'] = $dataInvoice;

        //Kirim Notifikasi

         //send to user peegurus
        $endpoint = "https://fcm.googleapis.com/fcm/send";
        $client = new \GuzzleHttp\Client();

        $fcm_token = $itemWarga->fcm_token;
        $title = 'Halo, '.$itemWarga->warga_nama_depan;
        $body = 'Lunas!. Tagihan '.$title_month.' '.$title_year.' telah dibayar.';

        Notifikasi::create([
            'warga_id' => $itemWarga->warga_id,
            'notif_title' => substr($title,0,100),
            'notif_body' => substr($body,0,255),
            'notif_page' => 'tagihan_lunas',
            'page_id' => $tag_id,
            'page_sts' => null,
            'notif_date' => Carbon::now()
        ]);

        //create json data
        $data_json = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound'	=> 'alarm.mp3'
                ],
                'data' => [
                    'id' => $tag_id,
                    'panic_tgl' => '',
                    'panic_jam' => '',
                    'panic_sts' => '',
                    'page' => 'tagihan_lunas'
                ],
                'to' => ''.$fcm_token.'',
                'collapse_key' => 'type_a',
            ];

        $requestAPI = $client->post($endpoint, [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
            'body' => json_encode($data_json)
        ]);

		// return json response
		return response()->json($response);
	}


    public function catat_pembayaran_manual_bulk(Request $request, Invoice $invoice)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;
        $txtPeriode = $request->txtPeriode;

        $tag_total = $request->tag_total;
        $tag_jumlah_bayar = $request->tag_jumlah_bayar;
        $tgl_bayar = $request->tag_tgl_bayar;
        $tag_catatan_bayar = $request->tag_catatan_bayar;

        $tgl_bayar = strtotime($tgl_bayar);
        $tag_tgl_bayar = date('Y-m-d',$tgl_bayar);

        if($tag_total != $tag_jumlah_bayar) {
            $response['message'] = 'Total tagihan tidak sama dengan jumlah pembayaran';
            return response()->json($response);
			exit();
        }

        $warga = new Warga;
        $itemWarga = $warga->get_detail($warga_id);

        $listPeriodeTunggakan = $invoice->getListPeriodeTunggakanWarga($warga_id, false);
        foreach($listPeriodeTunggakan as $tunggakan) {
            $tag_id = $tunggakan->tag_id;

            $dataInvoice = Invoice::find($tag_id);

            $dataInvoice->tag_id = $tag_id;
            $dataInvoice->tag_jumlah_bayar = $dataInvoice->tag_total;
            $dataInvoice->tag_catatan_bayar = $tag_catatan_bayar;
            $dataInvoice->tag_tgl_bayar = $tag_tgl_bayar;
            $dataInvoice->tag_cara_bayar = '1'; //pembayaran manual
            $dataInvoice->tag_status = '1'; //lunas
            $dataInvoice->save();

            $dataPeriodeTagihan = Periodetagihan::find($dataInvoice->pt_id);

            //pencatatan di table keuangan
            Keuangan::create([
                'tag_id' => $tag_id,
                'keu_tgl' => Carbon::now(),
                'keu_tgl_short' => date('Y-m-d'),
                'keu_status' => 1,
                'keu_sumbertujuan' => 'WARGA',
                'keu_deskripsi' => 'Pembayaran tagihan periode '.Periodetagihan::getMonthName($dataPeriodeTagihan->pt_bulan).'/'.$dataPeriodeTagihan->pt_tahun.' oleh : '.$itemWarga->warga_nama_depan.' '.$itemWarga->warga_nama_belakang,
                'keu_nominal' => $dataInvoice->tag_total,
                'wil_id' => $itemWarga->wil_id,
                'created_at' => Carbon::now()
            ]);
        }

		// response
		$response['status'] = "success";
		$response['message'] = "Tagihan ".$txtPeriode." atas nama ".$itemWarga->warga_nama_depan." telah lunas dibayar";
		$response['results'] = $itemWarga;

        //Kirim Notifikasi

         //send to user peegurus
        $endpoint = "https://fcm.googleapis.com/fcm/send";
        $client = new \GuzzleHttp\Client();

        $fcm_token = $itemWarga->fcm_token;
        $title = 'Halo, '.$itemWarga->warga_nama_depan;
        $body = 'Lunas!. Tagihan '.$txtPeriode.' telah dibayar.';

        Notifikasi::create([
            'warga_id' => $itemWarga->warga_id,
            'notif_title' => substr($title,0,100),
            'notif_body' => substr($body,0,255),
            'notif_page' => 'tagihan_lunas',
            'page_id' => $tag_id,
            'page_sts' => null,
            'notif_date' => Carbon::now()
        ]);

        //create json data
        $data_json = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound'	=> 'alarm.mp3'
                ],
                'data' => [
                    'id' => $tag_id,
                    'panic_tgl' => '',
                    'panic_jam' => '',
                    'panic_sts' => '',
                    'page' => 'tagihan_lunas'
                ],
                'to' => ''.$fcm_token.'',
                'collapse_key' => 'type_a',
            ];

        $requestAPI = $client->post($endpoint, [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
            'body' => json_encode($data_json)
        ]);

		// return json response
		return response()->json($response);
	}

    public function list_warga(Request $request, Warga $warga)
	{
		$wil_id = $request->wil_id;
		$warga_id = $request->warga_id;
		$keyword = $request->keyword;
		// validate param
		if($wil_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "wil_id is required fields";
            $response['results'] = [];
			return response()->json($response);
			exit();
		}

		// get data
		$warga = $warga->get_list($wil_id, $keyword, $warga_id);
		if(empty($warga))
		{
			$response['status'] = "success";
			$response['message'] = "Warga not found";
            $response['results'] = [];

			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $warga;

		// return json response
		return response()->json($response);

	}

    public function search_tunggakan(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;
        $pt_tahun = $request->pt_tahun;
        $pt_bulan = $request->pt_bulan;

        $error = false;

        if(empty($warga_id)) {
            $response['message'] = 'ID Warga tidak boleh kosong';
            $error = true;
        }

        if($error) {
            return response()->json($response);
        }

        $invoice = new Invoice;
        $tunggakanItem = array();
        // $tunggakanItem = $invoice->getItemTunggakanWargaPerPeriode($warga_id, $pt_bulan, $pt_tahun);

                // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tunggakanItem;
        $response['total_tunggakan'] = $invoice->getAmountTotalTunggakanWarga($warga_id);

		// return json response
		return response()->json($response);
    }



    // reject payment confirmation
    public function reject_payment_confirmation(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $bb_id = $request->bb_id;

        $buktiBayar = BuktiBayar::find($bb_id);
        if(!empty($buktiBayar->bb_status_valid)) {
            $response['message'] = 'Validasi gagal. Data sudah diproses oleh user lain';
            return response()->json($response);
        }

        $bbDetails = BBDetil::where('bb_id', $bb_id)->get();
        foreach($bbDetails as $detail) {
            $tagihan = Invoice::find($detail->tag_id);

            $tagihan->tag_id = $detail->tag_id;
            $tagihan->tag_status = null; //status '2 menjadi null
            $tagihan->confirm_no = null; //confirm_no dikosongkan
            $tagihan->save();
        }


        $buktiBayar->bb_id = $bb_id;
        $buktiBayar->bb_status_valid = 'T'; //tidak valid
        $buktiBayar->bb_validate_date = Carbon::now();
        $buktiBayar->save();

        $warga = new Warga;
        $itemWarga = $warga->get_detail($buktiBayar->warga_id);

        //Send Reject Notification
        //send to user warga
        $endpoint = "https://fcm.googleapis.com/fcm/send";
        $client = new \GuzzleHttp\Client();

        $fcm_token = $itemWarga->fcm_token;
        $title = 'Halo, '.$itemWarga->warga_nama_depan;
        $body = '[Rejected] Konfirmasi pembayaran periode '.$buktiBayar->bb_periode.' ditolak. Mohon konfirmasi ulang, Terima kasih.';

        Notifikasi::create([
            'warga_id' => $itemWarga->warga_id,
            'notif_title' => substr($title,0,100),
            'notif_body' => substr($body,0,255),
            'notif_page' => 'konfirmasi_pembayaran_manual_warga',
            'page_id' => $bb_id,
            'page_sts' => null,
            'notif_date' => Carbon::now()
        ]);

        //create json data
        $data_json = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound'	=> 'alarm.mp3'
                ],
                'data' => [
                    'id' => $bb_id,
                    'panic_tgl' => '',
                    'panic_jam' => '',
                    'panic_sts' => '',
                    'page' => 'konfirmasi_pembayaran_manual_warga'
                ],
                'to' => ''.$fcm_token.'',
                'collapse_key' => 'type_a',
            ];

        $requestAPI = $client->post($endpoint, [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
            'body' => json_encode($data_json)
        ]);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
    }

    // accept payment confirmation
    public function accept_payment_confirmation(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $bb_id = $request->bb_id;

        $buktiBayar = BuktiBayar::find($bb_id);
        if(!empty($buktiBayar->bb_status_valid)) {
            $response['message'] = 'Validasi gagal. Data sudah diproses oleh user lain';
            return response()->json($response);
        }

        $jumlahPembayaran = (double) $buktiBayar->bb_nominal;

        $bbDetails = BBDetil::where('bb_id', $bb_id)->get();
        $jmlDetail = count($bbDetails);
        $loop = 1;

        $warga = new Warga;
        $dataWarga = $warga->get_detail($buktiBayar->warga_id);

        foreach($bbDetails as $detail) {
            $tagihan = Invoice::find($detail->tag_id);

            $tagihan->tag_id = $detail->tag_id;
            $tagihan->tag_status = '1'; //status '2' menjadi '1' = lunas
            $tagihan->tag_cara_bayar = '3'; //konfirmasi_pembayaran_manual
            $tagihan->tag_tgl_bayar = $buktiBayar->bb_tgl;
            $tagihan->tag_catatan_bayar = $buktiBayar->bb_ket;
            $tagihan->payment_url = null;
            $tagihan->payment_url_created_date = null;
            $tagihan->tag_bukti_bayar = $buktiBayar->bb_bukti;
            $tagihan->tag_jumlah_bayar = $tagihan->tag_total;

            $jumlahPembayaran = $jumlahPembayaran - (double) $tagihan->tag_total;
            if($loop == $jmlDetail) { //last record
                $tagihan->tag_jumlah_bayar = $tagihan->tag_total + $jumlahPembayaran;
            }

            $loop++;
            $tagihan->save();

            //Tambahkan ke table keuangan
            Keuangan::create([
                'tag_id' => $tagihan->tag_id,
                'keu_tgl' => Carbon::now(),
                'keu_tgl_short' => date('Y-m-d'),
                'keu_status' => 1,
                'keu_sumbertujuan' => 'WARGA',
                'keu_deskripsi' => 'Pembayaran tagihan via konfirmasi manual periode '.$buktiBayar->bb_periode.' oleh : '.$dataWarga->warga_nama_depan.' '.$dataWarga->warga_nama_belakang,
                'keu_nominal' => $tagihan->tag_jumlah_bayar,
                'wil_id' => $dataWarga->wil_id,
                'created_at' => Carbon::now()
            ]);
        }

        $buktiBayar->bb_id = $bb_id;
        $buktiBayar->bb_status_valid = 'Y'; //valid
        $buktiBayar->bb_validate_date = Carbon::now();
        $buktiBayar->save();


        //Send Reject Notification
        //send to user peegurus
        $endpoint = "https://fcm.googleapis.com/fcm/send";
        $client = new \GuzzleHttp\Client();

        $fcm_token = $dataWarga->fcm_token;
        $title = 'Halo, '.$dataWarga->warga_nama_depan;
        $body = '[Accepted] Konfirmasi pembayaran periode '.$buktiBayar->bb_periode.' diterima. Terima kasih.';

        Notifikasi::create([
            'warga_id' => $dataWarga->warga_id,
            'notif_title' => substr($title,0,100),
            'notif_body' => substr($body,0,255),
            'notif_page' => 'konfirmasi_pembayaran_manual_warga',
            'page_id' => $bb_id,
            'page_sts' => null,
            'notif_date' => Carbon::now()
        ]);

        //create json data
        $data_json = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound'	=> 'alarm.mp3'
                ],
                'data' => [
                    'id' => $bb_id,
                    'panic_tgl' => '',
                    'panic_jam' => '',
                    'panic_sts' => '',
                    'page' => 'konfirmasi_pembayaran_manual_warga'
                ],
                'to' => ''.$fcm_token.'',
                'collapse_key' => 'type_a',
            ];

        $requestAPI = $client->post($endpoint, [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
            'body' => json_encode($data_json)
        ]);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
    }


    //CONTROLLER WARGA
    public function list_riwayat_periode_tagihan(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;

        $invoice = new Invoice;

        $tagihanList = array();
        $tagihanList = $invoice->getListPeriodeTagihanWarga($warga_id);

                // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tagihanList;
        $response['total_amount'] = $invoice->getAmountTotalTunggakanWarga($warga_id);

		// return json response
		return response()->json($response);
    }

    public function info_tunggakan(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;

        $invoice = new Invoice;
        $textPeriode = $invoice->getInfoPeriodeTunggakan($warga_id);

                // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['periode_tunggakan'] = $textPeriode;
        $response['total_tunggakan'] = $invoice->getAmountTotalTunggakanWarga($warga_id);

		// return json response
		return response()->json($response);
    }

    public function pembayaran_all_warga(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;

        $invoice = new Invoice;
        $listPeriodeTunggakan = $invoice->getListPeriodeTunggakanWarga($warga_id, false);
        foreach($listPeriodeTunggakan as $tunggakan) {
            $tag_id = $tunggakan->tag_id;

            $dataInvoice = Invoice::find($tag_id);
            $dataPeriodeTagihan = Periodetagihan::find($dataInvoice->pt_id);
            $dataWarga = Warga::find($dataInvoice->warga_id);

            $dataInvoice->tag_id = $tag_id;
            $dataInvoice->tag_jumlah_bayar = $dataInvoice->tag_total;
            $dataInvoice->tag_catatan_bayar = 'pay all periode';
            $dataInvoice->tag_tgl_bayar = Carbon::now();
            $dataInvoice->tag_cara_bayar = '1'; //pembayaran manual
            $dataInvoice->tag_status = '1'; //lunas
            $dataInvoice->save();

            Keuangan::create([
                'tag_id' => $tag_id,
                'keu_tgl' => Carbon::now(),
                'keu_tgl_short' => date('Y-m-d'),
                'keu_status' => 1,
                'keu_sumbertujuan' => 'WARGA',
                'keu_deskripsi' => 'Pembayaran tagihan periode '.Periodetagihan::getMonthName($dataPeriodeTagihan->pt_bulan).'/'.$dataPeriodeTagihan->pt_tahun.' oleh : '.$dataWarga->warga_nama_depan.' '.$dataWarga->warga_nama_belakang,
                'keu_nominal' => $dataInvoice->tag_total,
                'wil_id' => $dataWarga->wil_id,
                'created_at' => Carbon::now()
            ]);
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = [];

		// return json response
		return response()->json($response);
	}

    public function generate_payment_all_url(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());
        $warga_id = $request->warga_id;

        $invoice = new Invoice;
        $warga = new Warga;

        $listPeriodeTunggakan = $invoice->getListPeriodeTunggakanWarga($warga_id,false);
        $itemWarga = $warga->get_detail($warga_id);

        $paymentUrl = '';
        $arrTagIds = array();
        $totalAmount = 0;
        foreach($listPeriodeTunggakan as $tunggakan) {
            $arrTagIds[] = $tunggakan->tag_id;
            $totalAmount += (int) $tunggakan->tag_total;
        }

        $tagIds = join(",", $arrTagIds);
        $order_no = 'R2'.Carbon::now()->timestamp;
        //Generate Order
        GenerateOrder::create([
            'order_no' => $order_no,
            'tag_ids' => $tagIds
        ]);

        $paymentUrl = Payment::getPaymentUrl($order_no, $totalAmount, $itemWarga->warga_nama_depan, $itemWarga->user_email);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['paymentUrl'] = $paymentUrl;

		// return json response
		return response()->json($response);
	}

    public function kirim_email(Request $request){
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());
        $warga_id = $request->warga_id;

        $invoice = new Invoice;
        $warga = new Warga;

        $listPeriodeTunggakan = $invoice->getListPeriodeTunggakanWarga($warga_id, false);
        if(count($listPeriodeTunggakan) == 0) {
            $response['status'] = 'success';
            $response['message'] = 'Tidak ada data tagihan';
            return response()->json($response);
        }


        $itemWarga = $warga->get_detail($warga_id);

        $to_name = $itemWarga->warga_nama_depan." ".$itemWarga->warga_nama_belakang;
        $to_email = $itemWarga->user_email;
        $textPeriode = $invoice->getInfoPeriodeTunggakan($warga_id);

        $totalList = count($listPeriodeTunggakan);

        $emailCop = array();
        $emailCop['to_name'] = $to_name;
        $emailCop['to_email'] = $to_email;
        $emailCop['periode_tagihan'] = $textPeriode;
        $emailCop['wil_nama'] = $itemWarga->wil_nama;
        $emailCop['total_tagihan'] = $invoice->getAmountTotalTunggakanWarga($warga_id);
        $emailCop['warga_nama'] = $to_name;
        $emailCop['tgl_jatuh_tempo'] = Carbon::parse(($listPeriodeTunggakan[$totalList-1])->tag_due)->isoFormat('D MMMM Y');


        $logoWilayah = URL('public/img/logo_wilayah/default.png');;
        if(!empty($itemWarga->wil_logo)) {
            $logoWilayah = URL('public/img/logo_wilayah/'.$itemWarga->wil_logo);
        }

        $data = array();
        $i = 0;
        foreach($listPeriodeTunggakan as $tunggakan) {
            $tagihan = Invoice::find($tunggakan->tag_id);

            $data[$i]['wil_nama'] = $itemWarga->wil_nama;
            $data[$i]['wil_alamat'] = $itemWarga->wil_alamat;
            $data[$i]['wil_logo'] = $logoWilayah;

            $data[$i]['warga_nama'] = $to_name;
            $data[$i]['warga_alamat'] = $itemWarga->warga_alamat;

            $data[$i]['periode_tagihan'] = $tunggakan->month_name.' '.$tunggakan->pt_tahun;
            $data[$i]['tgl_tagihan'] = Carbon::parse($tagihan->send_date)->isoFormat('D MMMM Y');
            $data[$i]['tgl_jatuh_tempo'] = Carbon::parse($tagihan->tag_due)->isoFormat('D MMMM Y');

            $data[$i]['order_no'] = $tagihan->order_no;

            $data[$i]['tag_ipl'] = $tagihan->tag_ipl;
            $data[$i]['tag_listrik_total'] = $tagihan->tag_listrik_total;
            $data[$i]['tag_listrik_kwh'] = (int)$tagihan->tag_listrik_akhir - (int)$tagihan->tag_listrik_awal;
            $data[$i]['tag_listrik_per_kwh'] = $tagihan->tag_listrik_per_kwh;
            $data[$i]['tag_listrik_total_kwh'] = (int)$data[$i]['tag_listrik_kwh'] * (int)$tagihan->tag_listrik_per_kwh;
            $data[$i]['tag_listrik_abo'] = $tagihan->tag_listrik_abo;
            $data[$i]['tag_air_total'] = $tagihan->tag_air_total;
            $data[$i]['tag_air_m3'] = (int)$tagihan->tag_air_akhir - (int)$tagihan->tag_air_awal;
            $data[$i]['tag_air_per_m3'] = $tagihan->tag_air_per_m3;
            $data[$i]['tag_air_total_m3'] = (int)$data[$i]['tag_air_m3'] * (int)$tagihan->tag_air_per_m3;
            $data[$i]['tag_air_abo'] = $tagihan->tag_air_abo;
            $data[$i]['tag_lain'] = $tagihan->tag_lain;
            $data[$i]['tag_denda'] = $tagihan->tag_denda;
            $data[$i]['tag_total'] = $tagihan->tag_total;

            $data[$i]['month_name'] = $tunggakan->month_name;
            $data[$i]['pt_tahun'] = $tunggakan->pt_tahun;
            $data[$i]['name'] = $to_name;
            $i++;
        }

        $pdf = PDF::loadView('emails.tagihan-warga-pdf', ['data' => $data])->setPaper('a4', 'portrait');

        Mail::send('emails.tagihan', $emailCop, function($message) use ($to_name, $to_email, $textPeriode, $pdf) {
            $message->to($to_email, $to_name)
                    ->subject('Tagihan Bulanan '.$textPeriode.' atas nama '.$to_name)
                    ->from('rukun.id.99@gmail.com','Rukun')
                    ->attachData($pdf->output(), $textPeriode.".pdf");
        });

        // response
		$response['status'] = "success";
		$response['message'] = "OK";

        // return json response
		return response()->json($response);
    }

    // list konfirmasi pembayaran warga
    public function list_konfirmasi_pembayaran_warga(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;

        $invoice = new Invoice;

        $tunggakanList = array();
        $tunggakanList = $invoice->getListPeriodeTunggakanWarga($warga_id, false);

                // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tunggakanList;
        $response['total_amount'] = $invoice->getAmountTotalTunggakanWarga($warga_id);

		// return json response
		return response()->json($response);
    }

    //list waiting for validate
    public function list_konfirmasi_periode_validasi(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;
        $bb_id = $request->bb_id;

        if(empty($bb_id)) {
            $response['message'] = 'ID Bukti Bayar harus diisi';
            return response()->json($response);
        }

        $invoice = new Invoice;
        $buktiBayar = new BuktiBayar;
        $buktiBayarDetail = $buktiBayar->getDetail($bb_id);

        $warga = Warga::find($buktiBayarDetail->warga_id);

        $tunggakanList = array();
        $tunggakanList = $invoice->getListPeriodeWaitingForConfirmationValidate($warga_id, $bb_id);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tunggakanList;
        $response['total_amount'] = $invoice->getAmountTotalWaitingForConfirmationValidate($warga_id, $bb_id);
        $response['total_tagihan'] = $invoice->getAmountTotalTagihanPerBuktiBayar($bb_id);

        $response['bukti_bayar'] = $buktiBayarDetail;
        $response['warga'] = $warga;
		// return json response
		return response()->json($response);
    }

    public function list_bukti_bayar(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());
        $warga_id = $request->warga_id;
        $filter = $request->filter;
        $wil_id = $request->wil_id;

        $sort_dir = $request->sort_dir;

        $buktiBayar = new BuktiBayar;
        $buktiBayarList = $buktiBayar->getList($warga_id, $filter, $wil_id, $sort_dir);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $buktiBayarList;
        $response['total_amount'] = $buktiBayar->totalKonfirmasi($warga_id, $filter, $wil_id);

		// return json response
		return response()->json($response);
    }

    public function list_bukti_bayar_limited(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());
        $warga_id = $request->warga_id;
        $filter = $request->filter;
        $wil_id = $request->wil_id;

        $sort_dir = $request->sort_dir;

        $page = empty($request->page) ? 1 : $request->page;
        $limit = empty($request->limit) ? 20 : $request->limit;

        $buktiBayar = new BuktiBayar;
        $buktiBayarList = $buktiBayar->getListLimited($warga_id, $filter, $wil_id, $sort_dir, $page, $limit);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $buktiBayarList;
        $response['total_amount'] = $buktiBayar->totalKonfirmasi($warga_id, $filter, $wil_id);

		// return json response
		return response()->json($response);
    }

    public function konfirmasi_pembayaran_warga_per_periode(Request $request)
	{

        $response = array('status' => 'failed', 'message' => '', 'results' => array());

        $confirm_no = Carbon::now()->timestamp;

        $bb_tgl = $request->bb_tgl;
        $bb_bank = $request->bb_bank;
        $bb_rek_no = $request->bb_rek_no;
        $bb_rek_nama = $request->bb_rek_nama;
        $bb_cara_bayar = $request->bb_cara_bayar;
        $bb_nominal = $request->bb_nominal;
        $bb_ket = $request->bb_ket;
        $bb_periode = $request->bb_periode;
        $tag_id = $request->tag_id;
        $warga_id = $request->warga_id;

        $invoice = Invoice::find($tag_id);
        if(!empty($invoice->confirm_no)) {
            $response['message'] = 'Konfirmasi periode '.$bb_periode.' sudah dikirim sebelumnya. Silahkan menunggu sampai proses validasi selesai';
            // return json response
		    return response()->json($response);
        }

        $bbObj = new BuktiBayar;
        //cek apakah warga_id dan bb_periode sudah ada
        if($bbObj->isExistData($warga_id, $bb_periode)) {
            $response['message'] = 'Konfirmasi periode '.$bb_periode.' sudah dikirim sebelumnya. Silahkan menunggu sampai proses validasi selesai.';
            // return json response
		    return response()->json($response);
        }

        $img_file = '';
        $tgl_transfer = strtotime($bb_tgl);
        $tgl_transfer = date('Y-m-d',$tgl_transfer);

		if($request->file('bb_bukti')!='')
		{
			// destination path
			$destination_path = public_path('img/bukti_bayar/');
			$img = $request->file('bb_bukti');

			// upload
			$filename = $confirm_no;
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$filename.$ext");
			$img_file = "$filename.$ext";
		}else {
            $response['message'] = 'Bukti bayar harus diupload';
            // return json response
		    return response()->json($response);
        }

        $buktiBayar = BuktiBayar::create([
            'bb_tgl' => $tgl_transfer,
            'bb_bank' => $bb_bank,
            'bb_rek_no' => $bb_rek_no,
            'bb_rek_nama' => $bb_rek_nama,
            'bb_cara_bayar' => $bb_cara_bayar,
            'bb_nominal' => $bb_nominal,
            'bb_ket' => $bb_ket,
            'bb_periode' => $bb_periode,
            'bb_confirm_no' => $confirm_no,
            'bb_bukti' => $img_file,
            'warga_id' => $warga_id,
            'bb_created_at' => Carbon::now()
        ]);

        $invoice->tag_id = $tag_id;
        $invoice->confirm_no = $confirm_no;
        $invoice->tag_status = '2'; //Waiting for confirmed
        $invoice->save();

        BBDetil::create([
            'bb_id' => $buktiBayar->bb_id,
            'tag_id' => $invoice->tag_id
        ]);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $buktiBayar;


        //send notification to pengurus
        $warga = new Warga;
        $dataWarga = Warga::find($warga_id);
        $dataPengurus = $warga->get_pengurus_with_token($dataWarga->wil_id);

        foreach($dataPengurus as $pengurus) {
            //send to user warga
            $endpoint = "https://fcm.googleapis.com/fcm/send";
            $client = new \GuzzleHttp\Client();

            $fcm_token = $pengurus->fcm_token;
            $title = 'Konfirmasi Pembayaran Manual';
            $body = 'From : '.$dataWarga->warga_nama_depan.', Periode : '.$bb_periode.', Jumlah Transfer : Rp.'.number_format($bb_nominal, 0, ',','.');

            Notifikasi::create([
                'warga_id' => $pengurus->warga_id,
                'notif_title' => substr($title,0,100),
                'notif_body' => substr($body,0,255),
                'notif_page' => 'konfirmasi_pembayaran_manual',
                'page_id' => $buktiBayar->bb_id,
                'page_sts' => null,
                'notif_date' => Carbon::now()
            ]);

            //create json data
            $data_json = [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound'	=> 'alarm.mp3'
                    ],
                    'data' => [
                        'id' => $buktiBayar->bb_id,
                        'panic_tgl' => '',
                        'panic_jam' => '',
                        'panic_sts' => '',
                        'page' => 'konfirmasi_pembayaran_manual'
                    ],
                    'to' => ''.$fcm_token.'',
                    'collapse_key' => 'type_a',
                ];

            $requestAPI = $client->post($endpoint, [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                'body' => json_encode($data_json)
            ]);
        }


		// return json response
		return response()->json($response);
	}

    public function konfirmasi_pembayaran_warga_semua_periode(Request $request)
	{

        $response = array('status' => 'failed', 'message' => '', 'results' => array());

        $confirm_no = Carbon::now()->timestamp;

        $bb_tgl = $request->bb_tgl;
        $bb_bank = $request->bb_bank;
        $bb_rek_no = $request->bb_rek_no;
        $bb_rek_nama = $request->bb_rek_nama;
        $bb_cara_bayar = $request->bb_cara_bayar;
        $bb_nominal = $request->bb_nominal;
        $bb_ket = $request->bb_ket;
        $bb_periode = $request->bb_periode;
        $warga_id = $request->warga_id;

        $bbObj = new BuktiBayar;
        //cek apakah warga_id dan bb_periode sudah ada
        if($bbObj->isExistData($warga_id, $bb_periode)) {
            $response['message'] = 'Konfirmasi periode '.$bb_periode.' sudah dikirim sebelumnya. Silahkan menunggu sampai proses validasi selesai.';
            // return json response
		    return response()->json($response);
        }

        $invoice = new Invoice;
        $tunggakanList = $invoice->getListPeriodeTunggakanWarga($warga_id, false);
        if(count($tunggakanList) == 0) {
            $response['message'] = 'Tidak ada data tagihan yang perlu dikonfirmasi';
            // return json response
		    return response()->json($response);
        }

        $textPeriode = $invoice->getInfoPeriodeTunggakan($warga_id);
        $bb_periode = $textPeriode;

        $img_file = '';
        $tgl_transfer = strtotime($bb_tgl);
        $tgl_transfer = date('Y-m-d',$tgl_transfer);

		if($request->file('bb_bukti')!='')
		{
			// destination path
			$destination_path = public_path('img/bukti_bayar/');
			$img = $request->file('bb_bukti');

			// upload
			$filename = $confirm_no;
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$filename.$ext");
			$img_file = "$filename.$ext";
		}else {
            $response['message'] = 'Bukti bayar harus diupload';
            // return json response
		    return response()->json($response);
        }

        $buktiBayar = BuktiBayar::create([
            'bb_tgl' => $tgl_transfer,
            'bb_bank' => $bb_bank,
            'bb_rek_no' => $bb_rek_no,
            'bb_rek_nama' => $bb_rek_nama,
            'bb_cara_bayar' => $bb_cara_bayar,
            'bb_nominal' => $bb_nominal,
            'bb_ket' => $bb_ket,
            'bb_periode' => $bb_periode,
            'bb_confirm_no' => $confirm_no,
            'bb_bukti' => $img_file,
            'warga_id' => $warga_id,
            'bb_created_at' => Carbon::now()
        ]);

        foreach($tunggakanList as $tunggakan) {
            $inv = Invoice::find($tunggakan->tag_id);

            $inv->tag_id = $tunggakan->tag_id;
            $inv->confirm_no = $confirm_no;
            $inv->tag_status = '2'; //Waiting for confirmed
            $inv->save();

            BBDetil::create([
                'bb_id' => $buktiBayar->bb_id,
                'tag_id' => $tunggakan->tag_id
            ]);
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $buktiBayar;


        //send notification to pengurus
        $warga = new Warga;
        $dataWarga = Warga::find($warga_id);
        $dataPengurus = $warga->get_pengurus_with_token($dataWarga->wil_id);

        foreach($dataPengurus as $pengurus) {
            //send to user warga
            $endpoint = "https://fcm.googleapis.com/fcm/send";
            $client = new \GuzzleHttp\Client();

            $fcm_token = $pengurus->fcm_token;
            $title = 'Konfirmasi Pembayaran Manual';
            $body = 'From : '.$dataWarga->warga_nama_depan.', Periode : '.$bb_periode.', Jumlah Transfer : Rp.'.number_format($bb_nominal, 0, ',','.');

            Notifikasi::create([
                'warga_id' => $pengurus->warga_id,
                'notif_title' => substr($title,0,100),
                'notif_body' => substr($body,0,255),
                'notif_page' => 'konfirmasi_pembayaran_manual',
                'page_id' => $buktiBayar->bb_id,
                'page_sts' => null,
                'notif_date' => Carbon::now()
            ]);

            //create json data
            $data_json = [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound'	=> 'alarm.mp3'
                    ],
                    'data' => [
                        'id' => $buktiBayar->bb_id,
                        'panic_tgl' => '',
                        'panic_jam' => '',
                        'panic_sts' => '',
                        'page' => 'konfirmasi_pembayaran_manual'
                    ],
                    'to' => ''.$fcm_token.'',
                    'collapse_key' => 'type_a',
                ];

            $requestAPI = $client->post($endpoint, [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                'body' => json_encode($data_json)
            ]);
        }

		// return json response
		return response()->json($response);
	}
}
