<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Kk;
use App\Komplain;
use App\Notifikasi;
use App\Warga;
use App\Wilayah;
use Carbon\Carbon;

class KkController extends Controller
{

	/*==  List by komplain==*/
	public function list_komplain($komp_id)
	{

		// validate param
		if($komp_id == '')
		{
			$response['status'] = "error";
			$response['message'] = "ID Komplain tidak ada";
			return response()->json($response);

		}

        $listKomentar = KK::where('komp_id', $komp_id)->orderBy('kk_id','desc')->get();

        $data = array();
        $i = 0;
        foreach($listKomentar as $item) {

            $warga = Warga::find($item->kk_warga_id);

            $data[$i] = json_decode(json_encode($item), true);
            $data[$i]['warga_nama_depan'] = $warga->warga_nama_depan;
            $data[$i]['warga_nama_belakang'] = $warga->warga_nama_belakang;
            $data[$i]['created_date_formatted'] = Carbon::parse($item->create_date)->isoFormat("D MMMM Y HH:mm:ss");
            $i++;
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $data;

		// return json response
		return response()->json($response);
	}

	/*==  Detail ==*/
	public function detail($id, Request $request, Kk $kk)
	{
		// get data
		$kk = $kk->get_detail($id);
		if(empty($kk))
		{
			$response['status'] = "error";
			$response['message'] = "Komen Komplain not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kk;

		// return json response
		return response()->json($response);
	}



	/*== Add ==*/
	public function add(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

		// account warga
		$komp_id = $request->komp_id;
		$kk_komen = $request->kk_komen;
		$kk_warga_id = $request->kk_warga_id;


        $komplain = Komplain::find($komp_id);

        //cek subscription
        $wil_id = $komplain->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		$kk = new Kk;

		//set data pengurus
		$kk->komp_id = $komp_id;
		$kk->kk_komen = $kk_komen;
		$kk->kk_warga_id = $kk_warga_id;
        $kk->create_date = Carbon::now();
		$kk->save();

		$commentators = $kk->get_list_komentator_token($komp_id, $kk_warga_id);
		$warga = Warga::find($kk_warga_id);

		if(empty($commentators))
		{
			// // response
			$response['status'] = "failed";
			$response['message'] = "Pengurus belum dientry";

		}else{

			//send to user peegurus
			$endpoint = "https://fcm.googleapis.com/fcm/send";
			$client = new \GuzzleHttp\Client();

			foreach ($commentators as $rows) {

				$fcm_token = $rows->fcm_token;
                $title = $warga->warga_nama_depan.' membalas komentar';
                $body = substr($kk_komen,0,50)."...";

                Notifikasi::create([
                    'warga_id' => $rows->warga_id,
                    'notif_title' => substr($title,0,100),
                    'notif_body' => substr($body,0,255),
                    'notif_page' => 'komentar_komplain',
                    'page_id' => $komp_id,
                    'page_sts' => null,
                    'notif_date' => Carbon::now()
                ]);

				//create json data
				$data_json = [
				        'notification' => [
				        	'title' => $title,
				        	'body' => $body,
				        	'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
				        	'sound'	=> 'alarm.mp3'
				        ],
						'data' => [
				        	'id' => ''.$komp_id.'',
				        	'panic_tgl' => '',
				        	'panic_jam' => '',
				        	'panic_sts' => '',
				        	'page' => 'komentar_komplain'
				        ],
				        'to' => ''.$fcm_token.'',
						'collapse_key' => 'type_a',
				    ];

				$requestAPI = $client->post( $endpoint, [
			        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
			        'body' => json_encode($data_json)
			    ]);

			}

			$results = array(
				"kk_komen" => $kk_komen,
				"komentators" => $commentators
			);

			// response
			$response['status'] = "success";
			$response['message'] = "OK";
			$response['results'] = $results;
		}

		// return json response
		return response()->json($response);
	}

	/*== Update ==*/
	public function update(Request $request)
	{
		// account warga
		$kk_id = $request->kk_id;
		$kk_komen = $request->kk_komen;
		$kk = Kk::find($kk_id);

		$kk->kk_komen = $kk_komen;
		$kk->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		// return json response
		return response()->json($response);
	}

    /*== Update ==*/
	public function delete(Request $request)
	{

		$kk_id = $request->kk_id;
		Kk::find($kk_id)->delete();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}
}
