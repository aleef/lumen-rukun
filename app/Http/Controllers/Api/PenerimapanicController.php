<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Kp;
use Illuminate\Http\Request;
use Response;
use App\Penerimapanic;
use App\Wilayah;

class PenerimapanicController extends Controller
{
	private $ctrl = 'penerimapanic';
	private $title = 'Penerima panic';

	/*==  List ==*/
	public function list(Request $request, Penerimapanic $pp)
	{

		$wil_id = $request->wil_id;
        $kp_id = $request->kp_id;
        $keyword = $request->keyword;
		// get data
		$pp = $pp->get_list($wil_id, $kp_id, $keyword);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $pp;

		// return json response
		return response()->json($response);
	}

    public function list_pengurus_aktif(Request $request) {
        $wil_id = $request->wil_id;

        $pp = new Penerimapanic;
        $list = $pp->get_pengurus_aktif($wil_id);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $list;

		// return json response
		return response()->json($response);

    }

    public function list_pengurus_panic(Request $request) {
        $wil_id = $request->wil_id;
        $kp_id = $request->kp_id;

        $pp = new Penerimapanic;
        $list = $pp->get_pengurus_aktif($wil_id);
        $i = 0;
        $data = array();
        $dataEditing = array();

        foreach($list as $item) {
            $data[$i] = json_decode(json_encode($item), true);
            $pp = Penerimapanic::where('pengurus_id', $item->pengurus_id)
                                            ->where('kp_id',$kp_id)
                                            ->first();
            $pp_id = !empty($pp) ? $pp->pp_id : null;
            $data[$i]['checked'] = empty($pp_id) ? false: true;

            $dataEditing[$i]['pp_id'] = $pp_id;
            $dataEditing[$i]['pengurus_id'] = $item->pengurus_id;
            $dataEditing[$i]['mode'] = empty($pp_id) ? "new" : "edit";

            $i++;
        }
        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $data;
        $response['editing'] = $dataEditing;

		// return json response
		return response()->json($response);

    }



    public function list_kategori(Request $request) {
        $wil_id = $request->wil_id;
        $pengurus_id = $request->pengurus_id;


        $pp = new Penerimapanic;
        $list = $pp->get_list_kategori($wil_id, $pengurus_id);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $list;

		// return json response
		return response()->json($response);

    }

    public function saveall(Request $request)
	{
		// account warga
		$kp_id = $request->kp_id;


        $kp = Kp::find($kp_id);
        $wil_id = $kp->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		$checkboxs = $request->checkboxs;

        $listCheckBoxs = json_decode($checkboxs, true);

        foreach($listCheckBoxs as $item) {
            if($item['mode'] == 'add') {
                $pp = new Penerimapanic;
                $pp->kp_id = $kp_id;
                $pp->pengurus_id = $item['pengurus_id'];
                $pp->save();
            }elseif($item['mode'] == 'delete') {
                $pp = Penerimapanic::find($item['pp_id']);
                if(!empty($pp)) {
                    $pp->delete();
                }
            }
        }

		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}


	/*== Add ==*/
	public function add(Request $request)
	{
		// account warga
		$kp_id = $request->kp_id;
		$pengurus_id = $request->pengurus_id;

		$pp = new Penerimapanic;

		//set data pengurus
		$pp->kp_id = $kp_id;
		$pp->pengurus_id = $pengurus_id;
		$pp->save();

		$results = array(
			"kp_id" => $pp->kp_id,
			"pengurus_id" => $pp->pengurus_id
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
		//
		$pp_id = $request->pp_id;
		$kp_id = $request->kp_id;
		$pengurus_id = $request->pengurus_id;

		$pp = Penerimapanic::find($pp_id);

		//set data phonebook
		$pp->kp_id = $kp_id;
		$pp->pengurus_id = $pengurus_id;
		$pp->save();

		$results = array(
			"kp_id" => $pp->kp_id,
			"pengurus_id" => $pp->pengurus_id
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*==  Detail ==*/
	public function detail($id, Request $request, Penerimapanic $pp)
	{
		// get data
		$pp = Penerimapanic::find($id);
		if(empty($pp))
		{
			$response['status'] = "error";
			$response['message'] = "Penerima Panic not found";
			return response()->json($response);
			exit();
		}

		$results = array(
			"kp_id" => $pp->kp_id,
			"pengurus_id" => $pp->pengurus_id
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
		$pp_id = $request->pp_id;

		// get data
		$penerima = Penerimapanic::find($pp_id);
		// theme checking
		if(empty($penerima))
		{
			$response['status'] = "error";
			$response['message'] = "Penerima with ID : $pp_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
			// delete
			Penerimapanic::find($pp_id)->delete();
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the Penerima Panic";
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
