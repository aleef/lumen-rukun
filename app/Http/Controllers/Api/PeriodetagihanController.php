<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Periodetagihan;
use App\Invoice;
use App\Wilayah;

class PeriodetagihanController extends Controller
{

    public function list(Request $request, Periodetagihan $pt) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $sortdir = $request->sortdir;

        $ptList = $pt->getList($wil_id, $sortdir);
        if(empty($ptList)) {
            $response['status'] = "success";
		    $response['message'] = "Periode tagihan belum ada";
		    $response['results'] = array();
            return response()->json($response);
        }

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $ptList;

		// return json response
		return response()->json($response);
    }

    public function riwayat_pembayaran(Request $request, Periodetagihan $pt) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $pt_bulan = $request->pt_bulan;
        $pt_tahun = $request->pt_tahun;

        $sortdir = $request->sortdir;

        $ptList = $pt->getRiwayatPembayaran($wil_id, $pt_bulan, $pt_tahun, $sortdir);
        if(empty($ptList)) {
            $response['status'] = "success";
		    $response['message'] = "Riwayat pembayaran belum ada";
		    $response['results'] = array();
            return response()->json($response);
        }

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $ptList;

		// return json response
		return response()->json($response);
    }


    public function add(Request $request) {

        $response = array(
            'status' => 'failed',
            'message' => 'request failed',
            'results' => array(),
        );

        $pt_bulan = $request->pt_bulan;
        $pt_tahun = $request->pt_tahun;
        $wil_id = $request->wil_id;


        $pt = Periodetagihan::where([
            ['pt_tahun',$pt_tahun],
            ['pt_bulan',$pt_bulan],
            ['wil_id',$wil_id],
        ])->first();

        $periodeTagihan = new Periodetagihan;

        if($pt != null || !empty($pt)) { // sudah ada
            $response['status'] = 'success';
            $response['message'] = 'ID Ditemukan';
            $response['results'] = $periodeTagihan->getItem($pt->pt_id);
        }else {

            $periodeTagihan->pt_tahun = $pt_tahun;
            $periodeTagihan->pt_bulan = $pt_bulan;
            $periodeTagihan->wil_id = $wil_id;

            $periodeTagihan->save();

            $response['status'] = 'success';
            $response['message'] = 'Data berhasil disimpan';
            $response['results'] = $periodeTagihan->getItem($periodeTagihan->pt_id);
        }

        return response()->json($response);

    }

    public function delete(Request $request)
	{
		$pt_id = $request->pt_id;
		$response = array('status' => 'error', 'message' => "Error, can't delete tagihan");

        try {
            $periodeTagihan = Periodetagihan::findOrFail($pt_id);

            $wil_id = $periodeTagihan->wil_id;
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }

            $invoice = new Invoice;
            $listInvoice = $invoice->getList($pt_id);
            if(!empty($listInvoice)) {
                foreach($listInvoice as $item) {
                    Invoice::findOrFail($item->tag_id)->delete();
                }
                // response
                $response['message'] = "Periode tagihan berhasil dihapus dengan total ".count($listInvoice)." tagihan terhapus";
            }else {
                $response['message'] = "Periode tagihan berhasil dihapus dengan total 0 tagihan terhapus";
            }

            $periodeTagihan->delete();
            $response['status'] = "success";
            // return json response
            return response()->json($response);

        }
		catch(\Exception $e) {
            $response['message'] = $e->getMessage();
			return response()->json($response);
			exit();
		}

	}
}
