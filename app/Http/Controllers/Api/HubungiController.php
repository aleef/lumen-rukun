<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Hubungi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
use Carbon\Carbon;
use DateTime;
use Mail;
use PDF;
use File;
use Illuminate\Support\Facades\Storage;


class HubungiController extends Controller
{


    public function post(Request $request)
    {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $hub = new Hubungi;
        $hub->hub_hp = $request->hub_hp;
        $hub->hub_email = $request->hub_email;
        $hub->hub_nama = $request->hub_nama;
        $hub->hub_pesan = $request->hub_pesan;
        $hub->hub_status = '1';
        $hub->save();

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
        $columns_valid = array("hub_tgl", "hub_status", "hub_nama", "hub_hp", "hub_email", "hub_pesan",);
        if (!isset($columns_valid[$col])) {
            $order = null;
        } else {
            $order = $columns_valid[$col];
        }
        $rs = Hubungi::select('*');
        if ($search != '') {
            $rs = $rs->where('hubungi.hub_nama', 'ilike', "%$search%")
                ->orWhere('hubungi.hub_pesan', 'ilike', "%$search%");
        }
        if ($length != 0) {
            $rs = $rs->limit($length);
        }
        if (isset($order)) {
            $rs = $rs->orderBy($order);
        }
        if ($request->get('hub_status')) {
            $rs = $rs->where('hubungi.hub_status', $request->hub_status);
        }

        $rs = $rs->get();
        /*Status sudah ditanggapi apa belum (di CRM)
        1 = Request (default)
        2 = Dibikin Billing & Kirim Email (via CRM)
        3 = Sudah Dibayar*/
        $data = array();
        if (!empty($rs)) {
            foreach ($rs as $r) {
                $status = array('', 'Belum Dibaca', 'Belum Ditanggapi', 'Sudah Ditanggapi');
                
                $data[] = array(
                    (Carbon::parse($r->hub_tgl)->format('d-m-Y')),
                    $r->hub_nama,
                    $r->hub_hp,
                    $r->hub_email,
                    substr($r->hub_pesan,0,30).' ...',
                    $status[$r->hub_status],
                    '<a href="#" onclick="showEdit(' . $r->hub_id . ')" title="Lihat Detail" data-toggle="modal" data-id="' . $r->hub_id . '"><i class="fa fa-edit fa-lg text-primary"></i></a> '
                );
            }
            //total data

            $total_data =  Hubungi::count();
            //total filtered
            $total_fil = Hubungi::select(1);
            if ($search != '') {
                $total_fil = $total_fil->where('hubungi.hub_nama', 'ilike', "%$search%")
                    ->orWhere('hubungi.hub_pesan', 'ilike', "%$search%");
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
        $columns_valid = array("wil_nama", "hub_tgl", "hub_pesan", "hub_status", "hub_nama", "hub_hp", "hub_email");
        if (!isset($columns_valid[$col])) {
            $order = null;
        } else {
            $order = $columns_valid[$col];
        }

        $rs = DB::table('hubungi AS r')
            ->join('wilayah as w', 'r.wil_id', '=', 'w.wil_id')
            ->selectRaw('r.*, w.wil_nama')
            ->whereNotNull('r.wil_id');
        if ($search != '') {
            $rs = $rs->where('r.hub_nama', 'ilike', "%$search%")
                ->orWhere('w.wil_nama', 'ilike', "%$search%");
        }
        if ($length != 0) {
            $rs = $rs->limit($length);
        }
        if (isset($order)) {
            $rs = $rs->orderBy($order);
        }
        if ($request->get('hub_status')) {
            $rs = $rs->where('r.hub_status', $request->hub_status);
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
                if ($r->hub_invoice_file) {
                    $d_inv = '<a href="#" onclick="downloadPdf(this)" data-id="' . $r->hub_invoice_file . '" title="Download Invoice"><i class="fa fa-download fa-lg text-success"></i></a>';
                } else {
                    $d_inv = '';
                }
                $data[] = array(
                    $r->wil_nama,
                    (Carbon::parse($r->hub_tgl)->format('d-m-Y')),
                    $r->hub_pesan,
                    //number_format($r->hub_harga, 0, ',', '.'),
                    $status[$r->hub_status],
                    $r->hub_nama,
                    $r->hub_hp,
                    $r->hub_email,
                    '<a href="#" onclick="showSendMob(' . $r->hub_id . ')" title="Kirim Invoice" data-toggle="modal" data-id="' . $r->hub_id . '"><i class="fa fa-envelope fa-lg text-primary"></i></a> ' . $d_inv
                );
            }
            //total data

            $total_data =  DB::table('hubungi AS r')->whereNotNull('r.wil_id')->count();
            //total filtered
            $total_fil = DB::table('hubungi AS r')
                ->join('wilayah as w', 'r.wil_id', '=', 'w.wil_id')
                ->whereNotNull('r.wil_id');
            if ($search != '') {
                $total_fil = $total_fil->where('r.hub_nama', 'ilike', "%$search%")
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
    public function kirim_email(Request $request)
    {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $data = array();
        $paket = new PaketLangganan();

        $hub = Hubungi::find($request->hub_id);
        $itemPaket = $paket->getDetail($request->pl_id);
        $bil_jumlah = str_replace(",", ".", str_replace(".", "", $request->bil_jumlah));


        $to_name = $request->hub_nama;
        $to_email = $request->hub_email;

        $data['penanggung_jawab'] = $request->hub_nama;
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
    public function detail($id, Request $request)
    {
        // get data
        //$info = Hubungi::find($id);
        $info = DB::table('hubungi as r')
            ->selectRaw('r.*')
            ->where('r.hub_id', '=', $id)
            ->get();
        return $info;
    }
    public function update(Request $request)
    {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $hub_id = $request->hub_id;

        $hub = Hubungi::find($hub_id);
        $hub->hub_status = $request->hub_status;
        $hub->save();

        $response['status'] = "success";
        $response['message'] = "OK";
        $response['results'] = "Sukses mengubah data pesan";

        // return json response
        return response()->json($response);
    }/*== Delete ==*/
    public function delete(Request $request)
    {
        $hub_id = $request->hub_id;

        // get data
        $info = Hubungi::find($hub_id);
        // theme checking
        if (empty($info)) {
            $response['status'] = "error";
            $response['message'] = "Request Paket dengan ID : $hub_id tidak ditemukan";
            return response()->json($response);
            exit();
        }

        try {
            // delete
            Hubungi::find($hub_id)->delete();
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
