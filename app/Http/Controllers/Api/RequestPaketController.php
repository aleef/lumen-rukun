<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\RequestPaket;
use App\Billing;
use App\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
use Carbon\Carbon;
use App\Kab;
use App\Kec;
use App\Kel;
use App\PaketLangganan;
use App\Payment;
use App\Wilayah;
use DateTime;
use Mail;
use PDF;
use File;
use Illuminate\Support\Facades\Storage;


class RequestPaketController extends Controller
{

    public function has_processing_request(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        if(empty($wil_id)) {
            $response['message'] = "Wilayah ID tidak boleh kosong";
            return response()->json($response);
        }

        $rs = RequestPaket::where('wil_id',$wil_id);
        $list = $rs->where(function($q) use ($wil_id) {
            $q->where('rp_status','1')
            ->orWhere('rp_status','2');
        })->get();

		$response['status'] = "success";
		$response['message'] = "OK";
        $response['total_request'] = empty($list) ? 0 : count($list);

		// return json response
		return response()->json($response);
    }

    public function add(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $jml_request_warga = $request->jml_request_warga;

        if(empty($wil_id)) {
            $response['message'] = "Wilayah ID tidak boleh kosong";
            return response()->json($response);
        }

        if($jml_request_warga <= 450) {
            $response['message'] = "Request harus diatas 450";
            return response()->json($response);
        }

        $rp = new RequestPaket;
        $rp->wil_id = $wil_id;
        $rp->rp_tgl = date('Y-m-d');
        $rp->rp_jml_user = $jml_request_warga;
        $rp->rp_status = '1';
        $rp->save();

		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
    }

    public function add_crm(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $rp_hp = $request->rp_hp;
        $jml_request_warga = $request->rp_jml_user;

        if(empty($rp_hp)) {
            $response['message'] = "No.HP tidak boleh kosong";
            return response()->json($response);
        }

        if($jml_request_warga <= 450) {
            $response['message'] = "Request harus diatas 450";
            return response()->json($response);
        }

        $rp = new RequestPaket;
        $rp->rp_hp = $rp_hp;
        $rp->rp_email = $request->rp_email;
        $rp->rp_nama = $request->rp_nama;
        $rp->rp_nama_wilayah = $request->rp_nama_wilayah;
        $rp->rp_tgl = date('Y-m-d');
        $rp->rp_jml_user = $jml_request_warga;
        $rp->rp_status = '1';
        $rp->save();

		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
    }
    public function daftar_crm(Request $request)
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
        $columns_valid = array("rp_nama_wilayah", "rp_tgl", "rp_jml_user", "rp_status", "rp_nama", "rp_hp", "rp_email");
        if (!isset($columns_valid[$col])) {
            $order = null;
        } else {
            $order = $columns_valid[$col];
        }
        $rs = RequestPaket::select('*');
        if ($search != '') {
            $rs = $rs->where('request_paket.rp_nama', 'ilike', "%$search%")
            ->orWhere('request_paket.rp_nama_wilayah', 'ilike', "%$search%");
        }
        if ($length != 0) {
            $rs = $rs->limit($length);
        }
        if (isset($order)) {
            $rs = $rs->orderBy($order);
        }
        if ($request->get('rp_status')) {
            $rs = $rs->where('request_paket.rp_status', $request->rp_status);
        }

        $rs = $rs->get();
        /*Status sudah ditanggapi apa belum (di CRM)
        1 = Request (default)
        2 = Dibikin Billing & Kirim Email (via CRM)
        3 = Sudah Dibayar*/
        $data = array();
        if (!empty($rs)) {
            foreach ($rs as $r) {
                $status = array('', 'Request', 'Ditagih', 'Sudah Dibayar');
                if($r->rp_invoice_file){
                    $d_inv = '<a href="#" onclick="downloadPdf(this)" data-id="' . $r->rp_invoice_file . '" title="Download Invoice"><i class="fa fa-download fa-lg text-success"></i></a>';
                }else{
                    $d_inv = '';
                }
                $data[] = array(
                    $r->rp_nama_wilayah,
                    (Carbon::parse($r->rp_tgl)->format('d-m-Y')),
                    $r->rp_jml_user,
                    //number_format($r->rp_harga, 0, ',', '.'),
                    $status[$r->rp_status],
                    $r->rp_nama,
                    $r->rp_hp,
                    $r->rp_email,
                    '<a href="#" onclick="showSend(' . $r->rp_id . ')" title="Kirim Invoice" data-toggle="modal" data-id="' . $r->rp_id . '"><i class="fa fa-envelope fa-lg text-primary"></i></a> '.$d_inv
                );
            }
            //total data

            $total_data =  RequestPaket::count();
            //total filtered
            $total_fil = RequestPaket::select(1);
            if ($search != '') {
                $total_fil = $total_fil->where('request_paket.rp_nama', 'ilike', "%$search%")
                ->orWhere('request_paket.rp_nama_wilayah', 'ilike', "%$search%");
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
    //daftar crm req dari mobile (sdh ada wil_id)
    public function daftar_crm_mob(Request $request)
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
        $columns_valid = array("wil_nama", "rp_tgl", "rp_jml_user", "rp_status", "rp_nama", "rp_hp", "rp_email");
        if (!isset($columns_valid[$col])) {
            $order = null;
        } else {
            $order = $columns_valid[$col];
        }

        $rs = DB::table('request_paket AS r')
        ->join('wilayah as w', 'r.wil_id', '=', 'w.wil_id')
        ->selectRaw('r.*, w.wil_nama')
        ->whereNotNull('r.wil_id');
        if ($search != '') {
            $rs = $rs->where('r.rp_nama', 'ilike', "%$search%")
            ->orWhere('w.wil_nama', 'ilike', "%$search%");
        }
        if ($length != 0) {
            $rs = $rs->limit($length);
        }
        if (isset($order)) {
            $rs = $rs->orderBy($order);
        }
        if ($request->get('rp_status')) {
            $rs = $rs->where('r.rp_status', $request->rp_status);
        }

        $rs = $rs->get();
        /*Status sudah ditanggapi apa belum (di CRM)
        1 = Request (default)
        2 = Dibikin Billing & Kirim Email (via CRM)
        3 = Sudah Dibayar*/
        $data = array();
        if (!empty($rs)) {
            foreach ($rs as $r) {
                $status = array('', 'Request', 'Ditagih', 'Sudah Dibayar');
                if ($r->rp_invoice_file) {
                    $d_inv = '<a href="#" onclick="downloadPdf(this)" data-id="' . $r->rp_invoice_file . '" title="Download Invoice"><i class="fa fa-download fa-lg text-success"></i></a>';
                } else {
                    $d_inv = '';
                }
                $data[] = array(
                    $r->wil_nama,
                    (Carbon::parse($r->rp_tgl)->format('d-m-Y')),
                    $r->rp_jml_user,
                    //number_format($r->rp_harga, 0, ',', '.'),
                    $status[$r->rp_status],
                    $r->rp_nama,
                    $r->rp_hp,
                    $r->rp_email,
                    '<a href="#" onclick="showSendMob(' . $r->rp_id . ')" title="Kirim Invoice" data-toggle="modal" data-id="' . $r->rp_id . '"><i class="fa fa-envelope fa-lg text-primary"></i></a> '.$d_inv
                );
            }
            //total data

            $total_data =  DB::table('request_paket AS r')->whereNotNull('r.wil_id')->count();
            //total filtered
            $total_fil = DB::table('request_paket AS r')
                ->join('wilayah as w', 'r.wil_id', '=', 'w.wil_id')
                ->whereNotNull('r.wil_id');
            if ($search != '') {
                $total_fil =$total_fil->where('r.rp_nama', 'ilike', "%$search%")
                ->orWhere('w.wil_nama', 'ilike', "%$search%");
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
    /*== Add ==*/
    public function add_bill(Request $request)
    {
        try {

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

            $rp = RequestPaket::find($request->rp_id);
            $rp->rp_status = '2';
            $rp->rp_invoice_file = $request->bil_no.'-'.$request->wil_nama.'.pdf';
            $rp->save();

            // response
            $response['status'] = "success";
            $response['message'] = "OK";
            $response['results'] = "Sukses menyimpan invoice baru";
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

        if ($this->notif_mobile($billing->bil_id)) {
            return "sukses fcm";
        } else {
            return "gagal fcm";
        }
    }
    public function kirim_email_mob(Request $request)
    {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $data = array();
        $warga_id = $request->warga_id;

        $billing = new Billing;
        $warga = new Warga;

        $itemWarga = $warga->get_detail($warga_id);
        $itemBilling = $billing->getRecentBilling($itemWarga->wil_id);

        if (empty($itemBilling)) {
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

        $data['penanggung_jawab'] = $itemWarga->warga_nama_depan . " " . $itemWarga->warga_nama_belakang;
        $data['wil_nama'] = $itemWarga->wil_nama;
        $data['wil_alamat'] = $itemWarga->wil_alamat;
        $data['kabkota_nama'] = $kabkota->kabkota_nama;

        $data['nama_paket'] = $itemBilling->pl_nama;
        $data['nomor_tagihan'] = $itemBilling->bil_no;
        $data['periode_dari'] = Carbon::parse($itemBilling->bil_mulai)->isoFormat('D MMMM Y');
        $data['periode_sampai'] = Carbon::parse($itemBilling->bil_akhir)->isoFormat('D MMMM Y');
        $data['periode_tagihan'] = $data['periode_dari'] . " - " . $data['periode_sampai'];
        $data['tgl_tagihan'] = Carbon::parse($itemBilling->bil_date)->isoFormat('D MMMM Y');
        $data['tgl_jatuh_tempo'] = Carbon::parse($itemBilling->bil_due)->isoFormat('D MMMM Y');
        $data['jumlah_tagihan'] = "Rp." . number_format($itemBilling->bil_jumlah, 0, ',', '.');

        $pdf = PDF::loadView('emails.billingpdf', $data)->setPaper('a4', 'portrait');

        $destination_path = public_path('req_paket_inv/');
        $nama_file = $request->bil_no . '-' . $request->wil_nama . '.pdf';

        file_put_contents($destination_path . $nama_file, $pdf->output());

        Mail::send('emails.billingpdf_cop', $data, function ($message) use ($to_name, $to_email, $pdf, $data) {
            $message->to($to_email, $to_name)
                ->subject('Tagihan Aplikasi Rukun ' . $data['wil_nama'])
                ->from('rukun.id.99@gmail.com', 'Rukun')
                ->attachData($pdf->output(), $data['nomor_tagihan'] . ".pdf");
        });

        // response
        $response['status'] = "success";
        $response['message'] = "OK";

        // return json response
        return response()->json($response);
    }
    public function kirim_email(Request $request)
    {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $data = array();
        $paket = new PaketLangganan();

        $rp = RequestPaket::find($request->rp_id);
        $itemPaket = $paket->getDetail($request->pl_id);
        $bil_jumlah = str_replace(",", ".", str_replace(".", "", $request->bil_jumlah));


        $to_name = $request->rp_nama;
        $to_email = $request->rp_email;

        $data['penanggung_jawab'] = $request->rp_nama;
        $data['wil_nama'] = $request->wil_nama;
        $data['wil_alamat'] = '';
        $data['kabkota_nama'] = '';

        $data['nama_paket'] = $itemPaket->pl_nama;
        $data['nomor_tagihan'] = $request->bil_no;
        $data['periode_dari'] = Carbon::parse($request->bil_mulai)->isoFormat('D MMMM Y');
        $data['periode_sampai'] = Carbon::parse($request->bil_akhir)->isoFormat('D MMMM Y');
        $data['periode_tagihan'] = $data['periode_dari'] . " - " . $data['periode_sampai'];
        $data['tgl_tagihan'] = Carbon::parse($request->bil_date)->isoFormat('D MMMM Y');
        $data['tgl_jatuh_tempo'] = Carbon::parse($request->bil_due)->isoFormat('D MMMM Y');
        $data['jumlah_tagihan'] = "Rp." . number_format($bil_jumlah, 0, ',', '.');

        $pdf = PDF::loadView('emails.billingpdf', $data)->setPaper('a4', 'portrait');
        //Storage::put('public/req_paket_inv/'. $request->bil_no . '-' . $request->wil_nama .'.pdf', $pdf->output());
        $destination_path = public_path('req_paket_inv/');
        $nama_file = $request->bil_no . '-' . $request->wil_nama.'.pdf';

        file_put_contents($destination_path.$nama_file, $pdf->output());

        Mail::send('emails.billingpdf_cop', $data, function ($message) use ($to_name, $to_email, $pdf, $data) {
            $message->to($to_email, $to_name)
                ->subject('Tagihan Aplikasi Rukun ' . $data['wil_nama'])
                ->from('rukun.id.99@gmail.com', 'Rukun')
                ->attachData($pdf->output(), $data['nomor_tagihan'] . ".pdf");
        });
        

        // response
        $response['status'] = "success";
        $response['message'] = "OK";

        // return json response
        return response()->json($response);
    }
    //download pdf
    public function download_pdf_mob($id, Request $request)
    {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $data = array();
        $warga_id = $request->warga_id;

        $billing = new Billing;
        $warga = new Warga;

        $itemWarga = $warga->get_detail($warga_id);
        $itemBilling = $billing->getRecentBilling($itemWarga->wil_id);

        if (empty($itemBilling)) {
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

        $data['penanggung_jawab'] = $itemWarga->warga_nama_depan . " " . $itemWarga->warga_nama_belakang;
        $data['wil_nama'] = $itemWarga->wil_nama;
        $data['wil_alamat'] = $itemWarga->wil_alamat;
        $data['kabkota_nama'] = $kabkota->kabkota_nama;

        $data['nama_paket'] = $itemBilling->pl_nama;
        $data['nomor_tagihan'] = $itemBilling->bil_no;
        $data['periode_dari'] = Carbon::parse($itemBilling->bil_mulai)->isoFormat('D MMMM Y');
        $data['periode_sampai'] = Carbon::parse($itemBilling->bil_akhir)->isoFormat('D MMMM Y');
        $data['periode_tagihan'] = $data['periode_dari'] . " - " . $data['periode_sampai'];
        $data['tgl_tagihan'] = Carbon::parse($itemBilling->bil_date)->isoFormat('D MMMM Y');
        $data['tgl_jatuh_tempo'] = Carbon::parse($itemBilling->bil_due)->isoFormat('D MMMM Y');
        $data['jumlah_tagihan'] = "Rp." . number_format($itemBilling->bil_jumlah, 0, ',', '.');

        $pdf = PDF::loadView('emails.billingpdf', $data)->setPaper('a4', 'portrait');

        Mail::send('emails.billingpdf_cop', $data, function ($message) use ($to_name, $to_email, $pdf, $data) {
            $message->to($to_email, $to_name)
                ->subject('Tagihan Aplikasi Rukun ' . $data['wil_nama'])
                ->from('rukun.id.99@gmail.com', 'Rukun')
                ->attachData($pdf->output(), $data['nomor_tagihan'] . ".pdf");
        });

        // response
        $response['status'] = "success";
        $response['message'] = "OK";

        // return json response
        return response()->json($response);
    }
    public function download_pdf($id, Request $request)
    {

        //$rp = RequestPaket::find($id);
        //$nama_file = $rp->rp_invoice_file;

        //$file = Storage::disk('public')->get("req_paket_inv/" . $id);
        $file = public_path() . "/req_paket_inv/".$id;
        $headers = array('Content-Type: application/pdf',);
        
        return response()->download($file, $id, $headers);
        //return Storage::download('public/req_paket_inv/'.$nama_file, $nama_file, $headers);
       
    }
    /*==  Detail ==*/
    public function detail_mob($id, Request $request)
    {
        // get data
        //$info = RequestPaket::find($id);
        $info = DB::table('request_paket as r')
            ->join('wilayah as w', 'r.wil_id', '=', 'w.wil_id')
            ->join('warga as wa', 'w.wil_id','=','wa.wil_id')
            ->join('pengurus as p', 'wa.warga_id','=', 'p.warga_id')
            ->selectRaw('r.*, w.wil_id, w.wil_nama, wa.warga_id')
            ->where('r.rp_id', '=', $id)
            ->limit(1)
            ->get();
        return $info;
    }
    public function detail($id, Request $request)
    {
        // get data
        //$info = RequestPaket::find($id);
        $info = DB::table('request_paket as r')
            ->selectRaw('r.*')
            ->where('r.rp_id', '=', $id)
            ->get();
        return $info;
    }
    public function update(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $rp_hp = $request->rp_hp;
        $rp_id = $request->rp_id;
        $jml_request_warga = $request->rp_jml_user;


        if($jml_request_warga <= 450) {
            $response['message'] = "Request harus diatas 450";
            return response()->json($response);
        }

        $rp = RequestPaket::find($rp_id);
        $rp->rp_hp = $rp_hp;
        $rp->rp_email = $request->rp_email;
        $rp->rp_nama = $request->rp_nama;
        $rp->rp_nama_wilayah = $request->rp_nama_wilayah;
        //$rp->rp_tgl = date('Y-m-d');
        $rp->rp_jml_user = $jml_request_warga;
        $rp->rp_status = $request->rp_status;
        $rp->save();

		$response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = "Sukses mengubah data Request Paket " . $request->rp_nama_wilayah;

		// return json response
		return response()->json($response);
    }/*== Delete ==*/
    public function delete(Request $request)
    {
        $rp_id = $request->rp_id;

        // get data
        $info = RequestPaket::find($rp_id);
        // theme checking
        if (empty($info)) {
            $response['status'] = "error";
            $response['message'] = "Request Paket dengan ID : $rp_id tidak ditemukan";
            return response()->json($response);
            exit();
        }

        try {
            // delete
            RequestPaket::find($rp_id)->delete();
        } catch (\Exception $e) {
            // failed
            $response['status'] = "error";
            $response['message'] = "Gagal menghapus data Request Paket";
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
