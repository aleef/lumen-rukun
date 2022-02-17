<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Mk;
use App\User;
use App\Warga;
use App\Wilayah;
use Carbon\Carbon;

class MkController extends Controller
{
	private $ctrl = 'mk';
	private $title = 'Mk';

	/*==  List Data ==*/
	public function list(Request $request, Mk $mk)
	{
		$wil_id = $request->wil_id;
		$keyword = $request->keyword;

		// validate param
		if($wil_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "Wil_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$mk = $mk->get_list($wil_id);
		if($mk->isEmpty())
		{
			$response['status'] = "error";
			$response['message'] = "Masa Kepengurusan not found";
			return response()->json($response);
			exit();
		}

		$i=0;
        foreach($mk as $row)
        {
        	$result[$i]['mk_id'] = $row->mk_id;
        	$result[$i]['periode_mulai'] = Carbon::parse($row->mk_periode_mulai)->format('d M Y');
        	$result[$i]['periode_akhir'] = Carbon::parse($row->mk_periode_akhir)->format('d M Y');
        	$result[$i]['periode_mulai_date'] = $row->mk_periode_mulai;
        	$result[$i]['periode_akhir_date'] = $row->mk_periode_akhir;
        	$result[$i]['mk_status'] = $row->mk_status;
        	$i++;
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $result;

		// return json response
		return response()->json($response);

	}

	/*==  Detail ==*/
	public function detail($mk_id, Request $request, Mk $mk)
	{

		// validate param
		if($mk_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "mk_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$mk = Mk::find($mk_id);

		//print_r($mk);

		if(empty($mk))
		{
			$response['status'] = "error";
			$response['message'] = "Masa Kepengurusan not found";
			return response()->json($response);
			exit();
		}


		if($mk->mk_sk!='')
		{
			$mk_sk = URL('public/pengurus/sk/'.$mk->mk_sk);
		} else {
			$mk_sk = URL('public/pengurus/sk/default.pdf');
		}

		$results = array(
			// "wil_nama" => $mk->wil_nama,
			"mk_periode_mulai" => $mk->mk_periode_mulai,
			"mk_periode_akhir" => $mk->mk_periode_akhir,
			"mk_status" => $mk->mk_status,
			"mk_sk" => $mk_sk
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);

	}

	/*==  MK Active ==*/
	public function active($wil_id, Request $request, Mk $mk)
	{

		// validate param
		if($wil_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "wil_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$mk = $mk->get_active($wil_id);
		if(empty($mk))
		{
			$response['status'] = "error";
			$response['message'] = "Masa Kepengurusan not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $mk;

		// return json response
		return response()->json($response);

	}

	/*== add ==*/
	public function add(Request $request)
	{

		try
    	{
			$wil_id = $request->wil_id;

            $response = array('status' => 'failed', 'message' => '');
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }

			$mk_periode_mulai = Carbon::parse($request->mk_periode_mulai)->format('Y-m-d');
			$mk_periode_akhir = Carbon::parse($request->mk_periode_akhir)->format('Y-m-d');

			//validasi tanggal harus lebih besar dari periode sebelunya
			//get last mk
            $mk = Mk::orderBy('mk_id', 'desc')->where('wil_id', $wil_id)->first();

            $last_mk_pa = Carbon::parse($mk->mk_periode_akhir)->format('Y-m-d');

        	$date1 = Carbon::createFromFormat('Y-m-d', $mk_periode_mulai);
	        $date2 = Carbon::createFromFormat('Y-m-d', $last_mk_pa);

	        $periode_date = $date1->gt($date2);

        	if($periode_date == false){

        		// response
				$response['status'] = "error";
				$response['message'] = "Periode tidak sesuai";

				// return json response
				return response()->json($response);

        	}else{

        		$mk = new Mk;

				$mk->wil_id = $wil_id;
				$mk->mk_periode_mulai = $mk_periode_mulai;
				$mk->mk_periode_akhir = $mk_periode_akhir;
				$mk->mk_status = 0;

				$mk->save();

				// response
				$response['status'] = "success";
				$response['message'] = "OK";

				// return json response
				return response()->json($response);

        	}


		}
	    catch(\Exception $e)
	    {

	      	// response
			$response['status'] = "error";
			$response['message'] = $e->getMessage();

			// return json response
			return response()->json($response);
	    }

	}

	/*== Update ==*/
	public function update(Request $request, Warga $warga)
	{
		try
    	{
			$mk_id = $request->mk_id;
			//

			$mk_periode_mulai = $request->mk_periode_mulai;
			$mk_periode_akhir = $request->mk_periode_akhir;
			$mk_status = $request->mk_status;


			$mk = Mk::find($mk_id);

            $wil_id = $mk->wil_id;
            $response = array('status' => 'failed', 'message' => '');
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }

			//set data
			if($mk_periode_mulai!='')
				$mk->mk_periode_mulai = $mk_periode_mulai;
			if($mk_periode_akhir!='')
				$mk->mk_periode_akhir = $mk_periode_akhir;
			if($mk_status!='')
				$mk->mk_status = $mk_status;

			$mk->save();

			//jika 1 maka aktifkan, jika selain 1 maka non-aktifkan

			if($mk_status != 1)
			{
				//update status pengurus menjadi warga jika dinonaktifkan
				$wil_id = $mk->wil_id;
				$warga = $warga->get_pengurus($wil_id);

				foreach($warga as $row){
					$warga_id = $row->warga_id;
					$user_id = $row->user_id;
					$fcm_token = $row->fcm_token;

					//update pengurus ke warga
					$user = User::find($user_id);
					$user->user_type = 3;
					$user->save();

					//kirim notif untuk logout
					//
					$title = 'Status Kepengurusan';
					$body = 'Status Kepengurusan Anda sudah dinonaktifkan';

					//send to user peegurus
					$endpoint = "https://fcm.googleapis.com/fcm/send";
					$client = new \GuzzleHttp\Client();

					Notifikasi::create([
	                    'warga_id' => $warga_id,
	                    'notif_title' => $title,
	                    'notif_body' => $body,
	                    'notif_page' => 'pengurus',
	                    'page_id' => $warga_id,
	                    'page_sts' => '-',
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
					        	'id' => ''.$warga_id.'',
					        	'panic_tgl' => '',
					        	'panic_jam' => '',
					        	'panic_sts' => 'info',
					        	'page' => 'pengurus'
					        ],
					        'to' => ''.$fcm_token.'',
							'collapse_key' => 'type_a',
					    ];

					$requestAPI = $client->post( $endpoint, [
				        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
				        'body' => json_encode($data_json)
				    ]);

				}
			}else{
				//update status pengurus menjadi warga jika dinonaktifkan
				$wil_id = $mk->wil_id;
				$warga = $warga->get_pengurus_nonaktif($wil_id);

				foreach($warga as $row){
					$warga_id = $row->warga_id;
					$user_id = $row->user_id;

					//update pengurus ke warga
					$user = User::find($user_id);
					$user->user_type = 2;
					$user->save();
				}
			}

			// response
			$response['status'] = "success";
			$response['message'] = "OK";

			// return json response
			return response()->json($response);
		}
	    catch(\Exception $e)
	    {

	      	// response
			$response['status'] = "error";
			$response['message'] = $e->getMessage();

			// return json response
			return response()->json($response);
	    }
	}

	/*== Delete ==*/
	public function delete(Request $request)
	{
		$mk_id = $request->mk_id;

		try
		{
			// delete
			$mk = Mk::find($mk_id);

            $wil_id = $mk->wil_id;
            $response = array('status' => 'failed', 'message' => '');
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }


            $mk->delete();

			// response
			$response['status'] = "success";
			$response['message'] = "OK";

			// return json response
			return response()->json($response);
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the Masa Kepengurusan ".$e." ";
			return response()->json($response);
			exit();
		}
	}

}
