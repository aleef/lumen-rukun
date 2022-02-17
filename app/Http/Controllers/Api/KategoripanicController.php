<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Kp;
use App\Panic;
use App\Penerimapanic;
use App\Wilayah;

class KategoripanicController extends Controller
{
	private $ctrl = 'kategoripanic';
	private $title = 'Kategori panic';

	/*==  List ==*/
	public function list(Request $request, Kp $kp)
	{

		$wil_id = $request->wil_id;
		$keyword = $request->keyword;
		// get data
		$kp = $kp->get_list($wil_id, $keyword);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kp;

		// return json response
		return response()->json($response);
	}

    public function list_button(Request $request, Kp $kp)
	{

		$wil_id = $request->wil_id;
		$keyword = $request->keyword;
		// get data
		$kp = $kp->get_list_button($wil_id, $keyword);

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
		$wil_id = $request->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }


		$kp_kategori = $request->kp_kategori;

		$kp = new Kp;

		//set data pengurus
		$kp->wil_id = $wil_id;
		$kp->kp_kategori = $kp_kategori;
		$kp->save();


		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kp;

		// return json response
		return response()->json($response);
	}

	/*== Update ==*/
	public function update(Request $request)
	{
		//
		$kp_id = $request->kp_id;
		$kp_kategori = $request->kp_kategori;

		$kp = Kp::find($kp_id);

        $wil_id = $kp->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		//set data phonebook
		$kp->kp_kategori = $kp_kategori;
		$kp->save();

		$results = array(
			"kp_kategori" => $kp->kp_kategori
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*==  Detail ==*/
	public function detail($id, Request $request, Kp $kp)
	{
		// get data
		$kp = Kp::find($id);
		if(empty($kp))
		{
			$response['status'] = "error";
			$response['message'] = "Kategori Panic not found";
			return response()->json($response);
			exit();
		}

		$results = array(
			"wil_id" => $kp->wil_id,
			"kp_id" => $kp->kp_id,
			"kp_kategori" => $kp->kp_kategori
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
		$kp_id = $request->kp_id;

		// get data
		$kp = Kp::find($kp_id);
		// theme checking
		if(empty($kp))
		{
			$response['status'] = "error";
			$response['message'] = "Kategori Panic with ID : $kp_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
            $wil_id = $kp->wil_id;
            $response = array('status' => 'failed', 'message' => '');
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }

            Penerimapanic::where('kp_id',$kp_id)->delete();
            Panic::where('kp_id',$kp_id)->delete();
			// delete
			Kp::find($kp_id)->delete();

		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the Kategori Panic";
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
