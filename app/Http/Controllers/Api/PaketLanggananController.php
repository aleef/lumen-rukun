<?php
namespace App\Http\Controllers\Api;

use App\GenerateSubscribeOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
use App\PaketLangganan;
use App\Payment;
use App\Voucher;
use App\VoucherWil;
use App\Warga;
use App\Wilayah;
use Carbon\Carbon;

class PaketLanggananController extends Controller
{

    public function list(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $version = $request->version;
        if(empty($wil_id) || empty($version)) {
            $response['status'] = "success";
            $response['results'] = [];
            return response()->json($response);
        }

        $jml_warga = Warga::where('wil_id',$wil_id)->count();

        $list = PaketLangganan::where('pl_status','1')
                ->where('pl_manual','T')
                ->where('pl_maks_warga','>=',$jml_warga)
                ->orderBy('pl_id','asc')
                ->get();

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $list;

		// return json response
		return response()->json($response);
    }

    public function upgrade_list(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $version = $request->version;
        if(empty($wil_id) || empty($version)) {
            $response['status'] = "success";
            $response['results'] = [];
            return response()->json($response);
        }

        $jml_warga = Warga::where('wil_id',$wil_id)->count();

        $paketLangganan = new PaketLangganan;
        $list = $paketLangganan->getUpgradeList($wil_id, $jml_warga);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $list;

		// return json response
		return response()->json($response);
    }

    public function rincian_pembayaran(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $pl_id = $request->pl_id;


        $nominal_discount = 0;
        $txtDiscount = '';

        $paketLangganan = PaketLangganan::find($pl_id);

        //Lihat Voucher Terlebih Dahulu
        $voucherWil = VoucherWil::where('wil_id',$wil_id)
                ->where('vw_status','0')
                ->whereRaw("vw_berlaku_sd >= '".date('Y-m-d')."'")
                ->orderBy('vw_id','asc')
                ->limit(1)
                ->first();

        if(!empty($voucherWil)) {
            $voucher = Voucher::find($voucherWil->v_id);
            $nominal_discount = !empty($voucher->v_nilai_nominal) ? $voucher->v_nilai_nominal : ($voucher->v_nilai_persen * $paketLangganan->pl_harga/100);

            $txtDiscount = !empty($voucher->v_nilai_nominal) ? '' : $voucher->v_nilai_persen.' %';
        }

        $data = array();
        $data['nilai_paket'] = $paketLangganan->pl_harga;
        $data['nama_paket'] = $paketLangganan->pl_nama;
        $data['maks_warga'] = $paketLangganan->pl_maks_warga;
        $data['month_paket'] = $paketLangganan->pl_bulan;
        $data['nilai_diskon'] = $nominal_discount;
        $data['txt_diskon'] = $txtDiscount;
        $data['total_pembayaran'] = ($paketLangganan->pl_harga - $nominal_discount);

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $data;

		// return json response
		return response()->json($response);
    }

    public function generate_payment_url_with_voucher(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());
        $pl_id = $request->pl_id;
        $wil_id = $request->wil_id;
        $warga_id = $request->warga_id;

        $paymentUrl = '';

        $wilayah = Wilayah::find($wil_id);
        if($wilayah->wil_status == '4') {
            $response['message'] = 'Wilayah Anda sudah berlangganan';
            return response()->json($response);
        }

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

        if(!empty($voucherWil)) {
            $voucher = Voucher::find($voucherWil->v_id);
            $vw_id = $voucherWil->vw_id;
            $nominal_discount = !empty($voucher->v_nilai_nominal) ? $voucher->v_nilai_nominal : ($voucher->v_nilai_persen * $paketLangganan->pl_harga/100);
        }

        $order_no = 'NS'.Carbon::now()->timestamp;
        //Generate Order
        GenerateSubscribeOrder::create([
            'order_no' => $order_no,
            'pl_id' => $pl_id,
            'wil_id' => $wil_id,
            'warga_id' => $warga_id,
            'created_date' => Carbon::now(),
            'vw_id' => $vw_id,
            'nominal_discount' => $nominal_discount,
        ]);

        $total_pembayaran = ($paketLangganan->pl_harga - $nominal_discount);
        if($total_pembayaran <= 0) {
            // response
            $response['status'] = "failed";
            $response['message'] = "Nilai pembayaran tidak boleh nol atau minus";
            // return json response
            return response()->json($response);
        }

        $warga = Warga::find($warga_id);
        $paymentUrl = Payment::getPaymentUrl($order_no, $total_pembayaran, $wilayah->wil_nama.'('.$paketLangganan->pl_nama.')', $warga->warga_email);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['paymentUrl'] = $paymentUrl;

		// return json response
		return response()->json($response);
	}

    //yang terbaru generate_payment_url_with_voucher
    public function generate_payment_url(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());
        $pl_id = $request->pl_id;
        $wil_id = $request->wil_id;
        $warga_id = $request->warga_id;

        $paymentUrl = '';

        $wilayah = Wilayah::find($wil_id);
        if($wilayah->wil_status == '4') {
            $response['message'] = 'Wilayah Anda sudah berlangganan';
            return response()->json($response);
        }

        $order_no = 'NS'.Carbon::now()->timestamp;
        //Generate Order
        GenerateSubscribeOrder::create([
            'order_no' => $order_no,
            'pl_id' => $pl_id,
            'wil_id' => $wil_id,
            'warga_id' => $warga_id,
            'created_date' => Carbon::now()
        ]);

        $paketLangganan = PaketLangganan::find($pl_id);
        $warga = Warga::find($warga_id);

        $paymentUrl = Payment::getPaymentUrl($order_no, $paketLangganan->pl_harga, $wilayah->wil_nama.' ('.'Paket '.$paketLangganan->pl_ket_harga.')', $warga->warga_email);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['paymentUrl'] = $paymentUrl;

		// return json response
		return response()->json($response);
	}


    public function upgrade_langganan_payment_url_with_voucher(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());
        $pl_id = $request->pl_id;
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

        if(!empty($voucherWil)) {
            $voucher = Voucher::find($voucherWil->v_id);
            $vw_id = $voucherWil->vw_id;
            $nominal_discount = !empty($voucher->v_nilai_nominal) ? $voucher->v_nilai_nominal : ($voucher->v_nilai_persen * $paketLangganan->pl_harga/100);
        }

        $order_no = 'US'.Carbon::now()->timestamp;
        //Generate Order
        GenerateSubscribeOrder::create([
            'order_no' => $order_no,
            'pl_id' => $pl_id,
            'wil_id' => $wil_id,
            'warga_id' => $warga_id,
            'created_date' => Carbon::now(),
            'vw_id' => $vw_id,
            'nominal_discount' => $nominal_discount,
        ]);

        $total_pembayaran = ($paketLangganan->pl_harga - $nominal_discount);
        if($total_pembayaran <= 0) {
            // response
            $response['status'] = "failed";
            $response['message'] = "Nilai pembayaran tidak boleh nol atau minus";
            // return json response
            return response()->json($response);
        }

        $warga = Warga::find($warga_id);
        $wilayah = Wilayah::find($wil_id);

        $paymentUrl = Payment::getPaymentUrl($order_no, $total_pembayaran, $wilayah->wil_nama.'('.$paketLangganan->pl_nama.')', $warga->warga_email);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['paymentUrl'] = $paymentUrl;

		// return json response
		return response()->json($response);
	}

    //yang terbaru upgrade_langganan_payment_url_with_voucher
    public function upgrade_langganan_payment_url(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());
        $pl_id = $request->pl_id;
        $wil_id = $request->wil_id;
        $warga_id = $request->warga_id;

        $paymentUrl = '';

        $order_no = 'US'.Carbon::now()->timestamp;
        //Generate Order
        GenerateSubscribeOrder::create([
            'order_no' => $order_no,
            'pl_id' => $pl_id,
            'wil_id' => $wil_id,
            'warga_id' => $warga_id,
            'created_date' => Carbon::now()
        ]);

        $paketLangganan = PaketLangganan::find($pl_id);
        $warga = Warga::find($warga_id);
        $wilayah = Wilayah::find($wil_id);

        $paymentUrl = Payment::getPaymentUrl($order_no, $paketLangganan->pl_harga, $wilayah->wil_nama.' ('.'Paket '.$paketLangganan->pl_ket_harga.')', $warga->warga_email);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['paymentUrl'] = $paymentUrl;

		// return json response
		return response()->json($response);
	}

    //crm

    public function list_crm(Request $request)
    {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $list = PaketLangganan::where('pl_status', '1')
        ->orderBy('pl_id', 'asc')
        ->get();

        // response
        $response['status'] = "success";
        $response['message'] = "OK";
        $response['results'] = $list;

        // return json response
        return response()->json($response);
    }
    public function daftar_paket(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $length = $request->get("length");
        $search = $request->get('search')['value'];

        $order =  $request->get('order');


        $col = 0;
        $dir = "";
        if (!empty($order)) {
            foreach ($order as $o) {
                $col = $o['column'];
                $dir = $o['dir'];
            }
        }

        if ($dir != "asc" && $dir != "desc") {
            $dir = "asc";
        }
        $columns_valid = array("pl_nama", "pl_maks_warga", "pl_bulan", "pl_harga", "pl_mulai_berlaku", "pl_status");
        if (!isset($columns_valid[$col])) {
            $order = null;
        } else {
            $order = $columns_valid[$col];
        }
        $rs = PaketLangganan::select('*');
        if ($length != 0) {
            $rs = $rs->limit($length);
        }
        if (isset($order)) {
            $rs = $rs->orderBy($order);
        }

        $rs = $rs->get();

        $data = array();
        if (!empty($rs)) {
            foreach ($rs as $r) {
                $status = array('Tidak Aktif', 'Aktif');
                $used = DB::table('billing')->select('bil_id')
                            ->where('pl_id', '=', $r->pl_id)
                            ->count();
                if($used == 0){
                    $hapus = '<a href="#" id="hapus" data-id="' . $r->pl_id . '" data-nama="' . $r->pl_nama . '" title="Hapus"><i class="fa fa-trash fa-lg text-danger"></i></a>';
                }else{
                    $hapus = '';
                }

                $data[] = array(
                    $r->pl_nama,
                    $r->pl_maks_warga,
                    $r->pl_bulan,
                    number_format($r->pl_harga, 0, ',', '.'),
                    (Carbon::parse($r->pl_mulai_berlaku)->format('d-m-Y')),
                    $status[$r->pl_status],
                    '<a href="#" onclick="showEdit(' . $r->pl_id . ')" title="Edit" data-toggle="modal" data-id="' . $r->pl_id . '"><i class="fa fa-edit fa-lg text-success"></i></a> '.$hapus
                );
            }
            //total data

            $total_data =  PaketLangganan::count();
            //total filtered
            $total_fil = PaketLangganan::select(1);
            if($search!=''){
					$total_fil = $total_fil->where('pl_nama','ilike',"%$search%")
					->orWhere('pl_bulan','=',"$search");
			}

            $total_fil = $total_fil->count();

            $output = array(
                "draw" => $draw,
                "recordsTotal" => $total_data,
                "recordsFiltered" => $total_fil,
                "data" => $data
            );
        } else {
            $output = array(
                "draw" => $draw,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => $data
            );
        }
        return response()->json($output, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /*==  Detail ==*/
    public function detail($id, Request $request)
    {
        // get data
        //$info = PaketLangganan::find($id);
        $info = DB::table('paket_langganan AS p')
                ->join('billing AS b', 'p.pl_id','=','b.pl_id', 'left')
                ->selectRaw('p.*, count(b.pl_id) used')
                ->where('p.pl_id', '=', $id)
                ->groupBy('p.pl_id')
                ->get();
        return $info;
    }
    /*== Add ==*/
    public function add(Request $request)
    {
        try {

            $pl = new PaketLangganan();
            $pl->pl_nama = $request->pl_nama;
            $pl->pl_maks_warga = $request->pl_maks_warga;
            $pl->pl_bulan = $request->pl_bulan;
            $pl->pl_harga = str_replace(",", ".", str_replace(".", "", $request->pl_harga));
            $pl->pl_mulai_berlaku = $request->pl_mulai_berlaku;
            $pl->pl_status = $request->pl_status;
            $pl->pl_manual = $request->pl_manual;
            $pl->pl_ket_harga = $request->pl_ket_harga;
            $pl->pl_ket_free = $request->pl_ket_free;

            $pl->save();

            // response
            $response['status'] = "success";
            $response['message'] = "OK";
            $response['results'] = "Sukses menyimpan Paket Langganan baru";
            // return json response
            return response()->json($response);
        } catch (\Exception $e) {
            // response
            $response['status'] = "error";
            $response['message'] = "error";
            $response['results'] =  $e->getMessage();
            // return json response
            return response()->json($response);
        }
    }


    /*== Update ==*/
    public function update(Request $request)
    {

        // account warga
        $pl_id = $request->edit_id;

        $pl = PaketLangganan::find($pl_id);

        $pl->pl_nama = $request->pl_nama;
        $pl->pl_maks_warga = $request->pl_maks_warga;
        $pl->pl_bulan = $request->pl_bulan;
        $pl->pl_harga = str_replace(",", ".", str_replace(".", "", $request->pl_harga));
        $pl->pl_mulai_berlaku = $request->pl_mulai_berlaku;
        $pl->pl_status = $request->pl_status;
        $pl->pl_manual = $request->pl_manual;
        $pl->pl_ket_harga = $request->pl_ket_harga;
        $pl->pl_ket_free = $request->pl_ket_free;

        $pl->save();

        // response
        $response['status'] = "success";
        $response['message'] = "OK";
        $response['results'] = "Sukses mengubah data Paket Langganan " . $request->pl_nama;

        // return json response
        return response()->json($response);
    }

    /*== Delete ==*/
    public function delete(Request $request)
    {
        $pl_id = $request->pl_id;

        // get data
        $info = PaketLangganan::find($pl_id);
        // theme checking
        if (empty($info)) {
            $response['status'] = "error";
            $response['message'] = "Paket Langganan dengan ID : $pl_id tidak ditemukan";
            return response()->json($response);
            exit();
        }

        try {
            // delete
            PaketLangganan::find($pl_id)->delete();
        } catch (\Exception $e) {
            // failed
            $response['status'] = "error";
            $response['message'] = "Gagal menghapus data Paket Langganan";
            return response()->json($response);
            exit();
        }

        // response
        $response['status'] = "success";
        $response['message'] = "OK";
        //$response['results'] = $results;

        // return json response
        return response()->json($response);
    }

}
