<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Phonebook;
use App\Wilayah;

class PhonebookController extends Controller
{

	public function list(Request $request)
	{

        $response =  array('status' => 'error', 'message' => '', 'results' => []);

		$wil_id = $request->wil_id;
		$keyword = $request->keyword;

        // get data
		$list = Phonebook::where('wil_id',$wil_id);
        if(!empty($keyword)) {
            $list = $list->where(function($q) use ($keyword) {
                $q->where('pb_nama','ilike',"%$keyword%")
                ->orWhere('pb_nomor','ilike',"%$keyword%");
            });
        }

        $list = $list->orderBy('pb_nama','asc')->get();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $list;

		// return json response
		return response()->json($response);
	}

    public function list_limited(Request $request)
	{

        $response =  array('status' => 'error', 'message' => '', 'results' => []);

		$wil_id = $request->wil_id;
		$keyword = $request->keyword;

        $page = empty($request->page) ? 1 : $request->page;
        $limit = empty($request->limit) ? 20 : $request->limit;

        // get data
		$list = Phonebook::where('wil_id',$wil_id);
        if(!empty($keyword)) {
            $list = $list->where(function($q) use ($keyword) {
                $q->where('pb_nama','ilike',"%$keyword%")
                ->orWhere('pb_nomor','ilike',"%$keyword%");
            });
        }

        $list = $list->orderBy('pb_nama','asc')
        ->limit($limit)->offset(($page-1)*$limit)
        ->get();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $list;

		// return json response
		return response()->json($response);
	}

	public function detail(Request $request)
	{

        $pb_id = $request->pb_id;

		$pb = Phonebook::find($pb_id);
		if(empty($pb))
		{
			$response['status'] = "error";
			$response['message'] = "Phone not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $pb;

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

		$pb_nomor = $request->pb_nomor;
		$pb_nama = $request->pb_nama;
        $pb_keterangan = $request->pb_keterangan;

		$pb = new Phonebook;

		//set data pengurus
		$pb->wil_id = $wil_id;
		$pb->pb_nomor = $pb_nomor;
		$pb->pb_nama = $pb_nama;
        $pb->pb_keterangan = $pb_keterangan;
		$pb->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}


    public function add_bulk(Request $request)
	{
        // account warga
		$wil_id = $request->wil_id;

        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $checkboxs = $request->checkboxs;
        $listCheckBoxs = json_decode($checkboxs, true);

        foreach($listCheckBoxs as $item) {

            $pb = new Phonebook;

            //set data pengurus
            $pb->wil_id = $wil_id;
            $pb->pb_nomor = $item['phoneNumber'];
            $pb->pb_nama = $item['name'];
            $pb->pb_keterangan = '-';
            $pb->save();

            // if($item['mode'] == 'add') {
            //     $pp = new Penerimapanic;
            //     $pp->kp_id = $kp_id;
            //     $pp->pengurus_id = $item['pengurus_id'];
            //     $pp->save();
            // }elseif($item['mode'] == 'delete') {
            //     $pp = Penerimapanic::find($item['pp_id']);
            //     if(!empty($pp)) {
            //         $pp->delete();
            //     }
            // }
        }




		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}

	/*== Update ==*/
	public function update(Request $request)
	{
		// account warga
		$pb_id = $request->pb_id;
		$pb_nomor = $request->pb_nomor;
		$pb_nama = $request->pb_nama;
        $pb_keterangan = $request->pb_keterangan;

		$pb = Phonebook::find($pb_id);

        $wil_id = $pb->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $pb->pb_id = $pb_id;
		//set data phonebook
		$pb->pb_nomor = $pb_nomor;
		$pb->pb_nama = $pb_nama;
        $pb->pb_keterangan = $pb_keterangan;
        $pb->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}

	/*== Delete ==*/
	public function delete(Request $request)
	{
		$pb_id = $request->pb_id;

		// get data
		$pb = Phonebook::find($pb_id);

		// theme checking
		if(empty($pb))
		{
			$response['status'] = "error";
			$response['message'] = "Buku Telepon with ID : $pb_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
            $wil_id = $pb->wil_id;
            $response = array('status' => 'failed', 'message' => '');
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }

			// delete
			Phonebook::find($pb_id)->delete();
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the Phone";
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
