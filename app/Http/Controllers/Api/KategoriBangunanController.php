<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Response;
use App\Kb;
use App\Wilayah;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class KategoriBangunanController extends Controller
{

	/*==  List Data Kategori Bangunan==*/
	public function list(Request $request, Kb $kb)
	{
		$keyword = $request->keyword;
		$wil_id = $request->wil_id;

		// get data
		$kb = $kb->get_list($wil_id, $keyword);
		if($kb->isEmpty())
		{
			$response['status'] = "error";
			$response['message'] = "Kategori Bangunan not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kb;

		// return json response
		return response()->json($response);

	}

	/*== Add ==*/
	public function add(Request $request)
	{

        $response = array('status' => 'error', 'message' => '', 'results' => []);

		// account warga
		$wil_id = $request->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }


		$kb_keterangan = $request->kb_keterangan;
		$kb_tarif_ipl = $request->kb_tarif_ipl;

        $validator = Validator::make($request->all(), [
            'kb_keterangan' => 'unique:kategori_bangunan',
        ]);

        if($validator->fails()) {
            $response['message'] = "Kategori bangunan ".$kb_keterangan." sudah ada";
            return response()->json($response);
        }

		$kb = new Kb;

		//set data pengurus
		$kb->wil_id = $wil_id;
		$kb->kb_keterangan = $kb_keterangan;
		$kb->kb_tarif_ipl = $kb_tarif_ipl;
		$kb->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}

	/*== Update ==*/
	public function update(Request $request)
	{
		//
		$kb_id = $request->kb_id;
		$kb_keterangan = $request->kb_keterangan;
		$kb_tarif_ipl = $request->kb_tarif_ipl;

        $kb = Kb::find($kb_id);

        $wil_id = $kb->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $validator = Validator::make($request->all(), [
            'kb_keterangan' => 'unique:kategori_bangunan,kb_keterangan,'.$kb_id.",kb_id"
        ]);

        if($validator->fails()) {
            $response['message'] = "Kategori bangunan ".$kb_keterangan." sudah ada";
            return response()->json($response);
        }


		//set data
		$kb->kb_keterangan = $kb_keterangan;
		$kb->kb_tarif_ipl = $kb_tarif_ipl;
		$kb->save();

		$results = array(
			"kb_keterangan" => $kb->kb_keterangan,
			"kb_tarif_ipl" => $kb->kb_tarif_ipl
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}


	/*== Delete ==*/
	public function delete(Request $request)
	{
		$kb_id = $request->kb_id;

		// get data
		$kb = Kb::find($kb_id);
		// theme checking
		if(empty($kb))
		{
			$response['status'] = "error";
			$response['message'] = "Kategori bangunan with ID : $kb_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
            $wil_id = $kb->wil_id;
            $response = array('status' => 'failed', 'message' => '');
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }
			// delete
			Kb::find($kb_id)->delete();
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Tipe bangunan ini sudah digunakan datanya, sehingga tidak dapat dihapus";
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


	/*== get kategori bangunan by wilayah ==*/
	public function list_by_wilayah(Request $request)
	{
		$wil_kode = Str::upper($request->wil_kode);
		$kb = new Kb;
		$wilayah = new Wilayah;
		$kb_ = $kb->get_kb_wil($wil_kode);
		$wilayah_ = $wilayah->get_wilayah($wil_kode);

		if(count($kb_) == 0)
		{
			$response['status'] = "error";
			$response['message'] = "Silahkan Periksa kembali kode wilayah Anda";
			return response()->json($response);
			exit();
		}else{
			// response
			$response['status'] = "success";
			$response['message'] = "OK";
			$response['wilayah'] = $wilayah_;
			$response['results'] = $kb_;
			// return json response
			return response()->json($response);
		}

	}
}
