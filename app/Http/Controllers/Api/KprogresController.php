<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Kprogres;
use App\Warga;

class KprogresController extends Controller
{
	private $ctrl = 'kp';
	private $title = 'Komen Progres';

	/*==  List ==*/
	public function list(Request $request, Kprogres $kp) 
	{	

		$komp_id = $request->komp_id;
		$keyword = $request->keyword;

		// validate param
		if($komp_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "komp_id is required fields";
			return response()->json($response);
			exit();
		}
		// get data
		$kp = $kp->get_list($keyword, $komp_id);
		if(empty($kp))
		{
			$response['status'] = "error";
			$response['message'] = "Komen Komplain not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kp;
		
		// return json response
		return response()->json($response);
	}

	/*==  Detail ==*/
	public function detail($id, Request $request, Kprogres $kp) 
	{
		// get data
		$kp = $kp->get_detail($id);
		if(empty($kp))
		{
			$response['status'] = "error";
			$response['message'] = "Komplain Progres not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kp;
		
		// return json response
		return response()->json($response);
	}

	/*== Add ==*/
	public function add(Request $request) 
	{
		// account warga
		$komp_id = $request->komp_id;
		$progres_tgl = $request->progres_tgl;
		$progres_deskripsi = $request->progres_deskripsi;
		$progres_status = $request->progres_status;

		$kp = new Kprogres;

		//set data pengurus
		$kp->komp_id = $komp_id;
		$kp->progres_tgl = $progres_tgl;
		$kp->progres_deskripsi = $progres_deskripsi;
		$kp->progres_status = $progres_status;
		$kp->save();

		$results = array(
			"progres_deskripsi" => $progres_deskripsi,
			"progres_status" => $progres_status
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;
		
		// return json response
		return response()->json($response);
	}

	/*== Update ==*/
	public function update(Request $request) 
	{
		// field
		$progres_id = $request->progres_id;
		$progres_tgl = $request->progres_tgl;
		$progres_deskripsi = $request->progres_deskripsi;
		$progres_status = $request->progres_status;


		$kp = Kprogres::find($progres_id);

		//set data

		$kp->progres_tgl = $progres_tgl;
		$kp->progres_deskripsi = $progres_deskripsi;
		$kp->progres_status = $progres_status;
		$kp->save();

		$results = array(
			"progres_tgl" => $progres_tgl,
			"progres_deskripsi" => $progres_deskripsi,
			"progres_status" => $progres_status
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;
		
		// return json response
		return response()->json($response);
	}
}