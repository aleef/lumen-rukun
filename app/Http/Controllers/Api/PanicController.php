<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Response;
use App\Panic;
use App\Warga;
use App\Kp;
use App\Notifikasi;
use App\User;
use App\Pengurus;
use App\Penerimapanic;
use App\Wilayah;

class PanicController extends Controller
{

	/*==  List ==*/
	public function list(Request $request, Panic $panic)
	{

		$wil_id 	= $request->wil_id;
		$keyword 	= $request->keyword;
		$warga_id 	= $request->warga_id;

        // get data
		$panic = $panic->get_list($wil_id, $keyword, $warga_id);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $panic;

		// return json response
		return response()->json($response);
	}

    public function list_limited(Request $request)
	{

		$wil_id 	= $request->wil_id;
		$keyword 	= $request->keyword;
		$warga_id 	= $request->warga_id;

        $page = empty($request->page) ? 1 : $request->page;
        $limit = empty($request->limit) ? 20 : $request->limit;

        $panic = new Panic;
        // get data
		$list = $panic->get_list_limited($wil_id, $keyword, $warga_id, $page, $limit);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $list;

		// return json response
		return response()->json($response);
	}

	/*==  Detail ==*/
	public function detail($id, Request $request, Panic $panic)
	{
		// get data
		$panic = $panic->get_detail($id);
		if(empty($panic))
		{
			$response['status'] = "error";
			$response['message'] = "Panic not found";
			return response()->json($response);
			exit();
		}

        $results = array(
			"warga_id" => $panic->warga_id,
			"warga_nama_depan" => $panic->warga_nama_depan,
			"warga_nama_belakang" => $panic->warga_nama_belakang,
			"warga_alamat" => $panic->warga_alamat,
			"panic_tgl" => Carbon::parse($panic->panic_tgl)->format('d M Y'),
			"panic_jam" => $panic->panic_jam,
			"kp_kategori" => $panic->kp_kategori,
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*== Add ==*/
	public function add(Request $request, Penerimapanic $pengurus)
	{
		// account warga
		$kp_id = $request->kp_id;
		$warga_id = $request->warga_id;
		$panic_tgl = date('Y-m-d');
		$panic_jam = Carbon::now()->toTimeString();
		$panic_sts = 0;

        //get warga
		$warga = Warga::find($warga_id);

        $wil_id = $warga->wil_id;
        $response = array('status' => 'failed', 'message' => '');

        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $panic = new Panic;

		//set data
		$panic->kp_id = $kp_id;
		$panic->warga_id = $warga_id;
		$panic->panic_tgl = $panic_tgl;
		$panic->panic_jam = $panic_jam;
		$panic->panic_sts = $panic_sts;
		$panic->save();
		$panic_id = $panic->panic_id;


		$warga_nama = ucfirst($warga->warga_nama_depan).' '.ucfirst($warga->warga_nama_belakang);
        $warga_alamat = $warga->warga_alamat." ".$warga->warga_no_rumah;
        $warga_hp = $warga->warga_hp;

		$wil_id = $warga->wil_id;
		//get name of category
		$kp = Kp::find($kp_id);
		$kp_kategori = $kp->kp_kategori;

		// print_r($warga_nama);
		//get fcm pengurus
		$listPengurus = $pengurus->get_list_penerima($wil_id, $kp_id);

        $panic_tgl = Carbon::now()->isoFormat('D MMMM Y');

		//check
		if(empty($listPengurus))
		{
			// // response
			$response['status'] = "failed";
			$response['message'] = "Pengurus belum dientry";

		}else{

			//send to user peegurus
			$endpoint = "https://fcm.googleapis.com/fcm/send";
			$client = new \GuzzleHttp\Client();
			//

			//print_r($pengurus);

			foreach ($listPengurus as $rows) {

                Notifikasi::create([
                    'warga_id' => $rows->warga_id,
                    'notif_title' => 'Alarm Darurat : '.$warga_nama.' ',
                    'notif_body' => $kp_kategori,
                    'notif_page' => 'panic',
                    'page_id' => null,
                    'page_sts' => 'panic#'.$panic_id."#".$warga_nama."#".$warga_alamat."#".$warga_hp."#".$kp_kategori."#".$panic_tgl."#".$panic_jam,
                    'notif_date' => Carbon::now()
                ]);

				$fcm_token = $rows->fcm_token;

				//create json data
				$data_json = [
						//node notification tidak boleh dipake, karena background proses akan gagal,
						//title, body di pindahkan ke data. di flutter silahkan disesuaikan

						'to' => ''.$fcm_token.'',
                        'collapse_key' => 'type_a',
                        'priority'=> 'high',
				        'data' => [
							'title' => 'Alarm Darurat : '.$warga_nama.' ',
				        	'body' => $kp_kategori,
				        	'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
				        	'id' => ''.$panic_id.'',
				        	'panic_tgl' => ''.$panic_tgl.'',
				        	'panic_jam' => ''.$panic_jam.'',
				        	'panic_sts' => ''.$panic_sts.'',
				        	'page' => 'panic',
                            'text' => 'panic#'.$panic_id."#".$warga_nama."#".$warga_alamat."#".$warga_hp."#".$kp_kategori."#".$panic_tgl."#".$panic_jam
				        ],
						'apns' => [
							'headers' => [
							  'apns-priority' => '5',
							  'apns-push-type' => 'background',
							],
							'payload' => [
							  'aps' => [
								'content-available' => 1,
							  ],
							],
						  ],
				    ];

				$requestAPI = $client->post( $endpoint, [
			        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
			        'body' => json_encode($data_json)
			    ]);

			}

			$results = array(
				"warga_id" => $warga_id
			);

			// // response
			$response['status'] = "success";
			$response['message'] = "Kirim Panic Sukses";
			$response['results'] = $results;

			// return json response
			return response()->json($response);
		}

	}

	/*== Update ==*/
	public function update(Request $request)
	{
		// account warga
		$panic_id = $request->panic_id;
		$panic_tgl = $request->panic_tgl;
		$panic_jam = $request->panic_jam;
		$panic_sts = $request->panic_sts;

		// validate param
		// if($ki_id=='')
		// {
		// 	$response['status'] = "error";
		// 	$response['message'] = "Kategori Info are required fields";
		// 	return response()->json($response);
		// 	exit();
		// }

		$panic = Panic::find($panic_id);

		//set data pengurus
		$panic->panic_sts = $panic_sts;
		$panic->save();

		$results = array(
			"panic_sts" => $panic_sts
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}
}
