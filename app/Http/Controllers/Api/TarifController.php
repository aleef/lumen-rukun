<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Tarif;
use App\Wilayah;

class TarifController extends Controller
{

    public function list(Request $request, Tarif $tarif) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;

        $tarifList = $tarif->getList($wil_id);
        if(empty($tarifList)) {
            $response['status'] = "success";
		    $response['message'] = "Tarif Belum Ada";
		    $response['results'] = array();
            return response()->json($response);
        }

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tarifList;

		// return json response
		return response()->json($response);
    }

    public function update(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $tarif_id = (int) $request->tarif_id;

        $tarif_nama = $request->tarif_nama;
        $tarif_nilai = (double) $request->tarif_nilai;

        $tarif = Tarif::find($tarif_id);
        $wil_id = $tarif->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $tarif->tarif_id = $tarif_id;
        $tarif->tarif_nama = $tarif_nama;
        $tarif->tarif_nilai = $tarif_nilai;
        $tarif->save();

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tarif;

		// return json response
		return response()->json($response);
    }

    public function getNilai(Request $request, Tarif $tarif) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $tarif_nama = $request->tarif_nama;
        $wil_id = $request->wil_id;

        $tarifNilai = $tarif->getNilai($tarif_nama);
        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tarifNilai;

		// return json response
		return response()->json($response);
    }
}
