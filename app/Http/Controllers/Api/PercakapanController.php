<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Percakapan;
use App\Pesan;
use App\Warga;
use App\User;

class PercakapanController extends Controller
{
	private $ctrl = 'percakapan';
	private $title = 'Percakapan';

	/*==  List ==*/
	public function list(Request $request, Percakapan $percakapan) 
	{	

		$wil_id 	= $request->wil_id;
		$keyword 	= $request->keyword;
		$warga_id 	= $request->warga_id;
		// get data
		$percakapan = $percakapan->get_list($wil_id, $keyword, $warga_id);
		if(empty($percakapan))
		{
			$response['status'] = "error";
			$response['message'] = "Panic not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $percakapan;
		
		// return json response
		return response()->json($response);
	}

	/*==  Detail ==*/
	public function detail(Request $request, Percakapan $percakapan) 
	{
		// get data
		$percakapan_id 	= $request->percakapan_id;

		$percakapan = $percakapan->get_detail($percakapan_id);

		if(empty($percakapan))
		{
			$response['status'] = "error";
			$response['message'] = "Panic not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $percakapan;
		
		// return json response
		return response()->json($response);
	}

	/*== Add ==*/
	public function add(Request $request, Pesan $pesan, User $user, Percakapan $percakapan) 
	{
			//create percakapan
			$wil_id = $request->wil_id;
			$warga_id = $request->warga_id;
			$second_warga_id = $request->second_warga_id;
			//
			$pesan_text = $request->pesan_text;
			//
			$identity = $request->identity; // percakapan identity

			//cek apakah sudah percakapan sebelumnya
			$percakapan = $percakapan->get_detail_by_warga($identity);

			// echo "warga_id :" .$warga_id;
			// print_r($percakapan);

			if(empty($percakapan))
			{
				// echo "null";
				//buat percakapan
				$percakapan = new Percakapan;
				$pesan = new Pesan;

				// set percakapan
				$percakapan->wil_id = $wil_id;
				$percakapan->warga_id = $warga_id;
				$percakapan->second_warga_id = $second_warga_id;
				$percakapan->created_at = date('Y-m-d');
				$percakapan->identity = $identity;
				$percakapan->save();
				$percakapan_id = $percakapan->percakapan_id;

				// set pesan
				$pesan->warga_id = $warga_id;
				$pesan->percakapan_id = $percakapan_id;
				$pesan->pesan_text = $pesan_text;
				$pesan->created_at = date('Y-m-d');
				$pesan->time_at = date('H:i:s');
				$pesan->pesan_read = '1'; // 0: jam 1: server 3:terkirim 4:read
				$pesan->save();
				$pesan_id = $pesan->pesan_id;

				// get warga
				$warga = Warga::find($warga_id);
				// $warga_nama = ucfirst($warga->warga_nama_depan).' '.ucfirst($warga->warga_nama_belakang);
				$warga_nama = $warga->warga_nama_depan.' '.$warga->warga_nama_belakang;


				// get token
				$user = $user->get_detail_warga($second_warga_id);
				$fcm_token = $user->fcm_token;

				//send to warga
				$endpoint = "https://fcm.googleapis.com/fcm/send";
				$client = new \GuzzleHttp\Client();
				//

				//create json data
				$data_json = [
							'notification' => [
							'title' => 'Pesan dari '.$warga_nama.'',
							'body' => $pesan_text,
							'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
							'sound'	=> 'alarm.mp3'
						],
							'data' => [
							'id' => $pesan_id,
							'percakapan_id' => $percakapan_id,
							'pesan_text' => $pesan_text,
							'warga_id' => $warga_id, // warga id pengirim 
							'second_warga_id' => $second_warga_id, // warga id tujuan
							'warga_nama' => $warga_nama,
							'created_at' => date('Y-m-d'),
							'time_at' => date('H:i:s'),
							'identity' => $identity,
							'page' => 'percakapan_baru'
						],
							'to' => ''.$fcm_token.''
				];

				$requestAPI = $client->post( $endpoint, [
					'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
					'body' => json_encode($data_json)
				]);

				// response
				$response['status'] = "success";
				$response['percakapan_id'] = $percakapan_id;
				$response['message'] = "Percakapan baru berhasil dibuat";
				$response['fmc'] = $fcm_token;

				// return json response
				return response()->json($response);

			}else{

				// buat pesan baru dipercakapan terkait
				$percakapan_id = $percakapan->percakapan_id;

				// cek apakah ada pesan sebelumnya yang belum terkirim

				$pesan_pending = $pesan->get_list_pesan_by_percakapan($percakapan_id);

				// print_r('Percakapan'. $percakapan_id);
				// print_r('Data'. $pesan_pending);
				//pritn

				if($pesan_pending->isEmpty()){

					
					// echo "New Message";

					$pesan = new Pesan;
					// set pesan
					$pesan->warga_id = $warga_id;
					$pesan->percakapan_id = $percakapan_id;
					$pesan->pesan_text = $pesan_text;
					$pesan->created_at = date('Y-m-d');
					$pesan->time_at = date('H:i:s');
					$pesan->pesan_read = '1'; // 0: jam 1: server 3:terkirim 4:read
					$pesan->save();
					$pesan_id = $pesan->pesan_id;

					// get warga
					$warga = Warga::find($warga_id);
					$warga_nama = $warga->warga_nama_depan.' '.$warga->warga_nama_belakang;

					// get token
					$user = $user->get_detail_warga($second_warga_id);
					$fcm_token = $user->fcm_token;

					// send to warga
					$endpoint = "https://fcm.googleapis.com/fcm/send";
					$client = new \GuzzleHttp\Client();
					//

					// create json data
					$data_json = [
								'notification' => [
								'title' => 'Pesan dari '.$warga_nama.'',
								'body' => $pesan_text,
								'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
								'sound'	=> 'alarm.mp3'
							],
								'data' => [
								'id' => $pesan_id,
								'percakapan_id' => $percakapan_id,
								'pesan_text' => $pesan_text,
								'warga_id' => $warga_id, // warga id pengirim 
								'second_warga_id' => $second_warga_id, // warga id tujuan
								'warga_nama' => $warga_nama,
								'created_at' => date('Y-m-d'),
								'time_at' => date('H:i:s'),
								'identity' => $identity,
								'page' => 'percakapan_lama'
							],
								'to' => ''.$fcm_token.''
					];

					$requestAPI = $client->post( $endpoint, [
						'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
						'body' => json_encode($data_json)
					]);

					// response
					$response['status'] = "success";
					$response['percakapan_id'] = $percakapan_id;
					$response['message'] = "Pesan berhasil disimpan";
					$response['fmc'] = $fcm_token;

					// return json response
					return response()->json($response);
					

				}else{

					// echo 'no null';
					$user = new User;

					foreach ($pesan_pending as $row) {
						$pesan_id_pending = $row->pesan_id;
						$pesan_text_pending = $row->pesan_text;
						$warga_id_pending = $row->warga_id;

						// get warga
						$warga = Warga::find($warga_id_pending);
						$warga_nama = $warga->warga_nama_depan.' '.$warga->warga_nama_belakang;

						// get token
						$user = $user->get_detail_warga($second_warga_id);
						$fcm_token = $user->fcm_token;

						//send to warga
						$endpoint = "https://fcm.googleapis.com/fcm/send";
						$client = new \GuzzleHttp\Client();
						//

						// create json data
						$data_json = [
									'notification' => [
									'title' => 'Pesan dari '.$warga_nama.'',
									'body' => $pesan_text_pending,
									'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
									'sound'	=> 'alarm.mp3'
								],
									'data' => [
									'id' => $pesan_id_pending,
									'percakapan_id' => $percakapan_id,
									'pesan_text' => $pesan_text_pending,
									'warga_id' => $warga_id, // warga id pengirim 
									'second_warga_id' => $second_warga_id, // warga id tujuan
									'warga_nama' => $warga_nama,
									'created_at' => date('Y-m-d'),
									'time_at' => date('H:i:s'),
									'identity' => $identity,
									'page' => 'percakapan_lama'
								],
									'to' => ''.$fcm_token.''
						];

						$requestAPI = $client->post( $endpoint, [
							'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
							'body' => json_encode($data_json)
						]);

						// // response
						// $response['status'] = "success";
						// $response['percakapan_id'] = $percakapan_id;
						// $response['message'] = "Pesan berhasil dikirim kembali";
						// $response['fmc'] = $fcm_token;

						// // return json response
						// return response()->json($response

						if($requestAPI){
							//
							$user = new User;
							$pesan = new Pesan;
							// set pesan
							$pesan->warga_id = $warga_id;
							$pesan->percakapan_id = $percakapan_id;
							$pesan->pesan_text = $pesan_text;
							$pesan->created_at = date('Y-m-d');
							$pesan->time_at = date('H:i:s');
							$pesan->pesan_read = '1'; // 0: jam 1: server 3:terkirim 4:read
							$pesan->save();
							$pesan_id = $pesan->pesan_id;

							// get warga
							$warga = Warga::find($warga_id);
							$warga_nama = $warga->warga_nama_depan.' '.$warga->warga_nama_belakang;

							// get token
							$user = $user->get_detail_warga($second_warga_id);
							$fcm_token = $user->fcm_token;

							// send to warga
							$endpoint = "https://fcm.googleapis.com/fcm/send";
							$client = new \GuzzleHttp\Client();
							//

							// create json data
							$data_json = [
										'notification' => [
										'title' => 'Pesan dari '.$warga_nama.'',
										'body' => $pesan_text,
										'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
										'sound'	=> 'alarm.mp3'
									],
										'data' => [
										'id' => $pesan_id,
										'percakapan_id' => $percakapan_id,
										'pesan_text' => $pesan_text,
										'warga_id' => $warga_id, // warga id pengirim 
										'second_warga_id' => $second_warga_id, // warga id tujuan
										'warga_nama' => $warga_nama,
										'created_at' => date('Y-m-d'),
										'time_at' => date('H:i:s'),
										'identity' => $identity,
										'page' => 'percakapan_lama'
									],
										'to' => ''.$fcm_token.''
							];

							$requestAPI = $client->post( $endpoint, [
								'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
								'body' => json_encode($data_json)
							]);

							// response
							$response['status'] = "success";
							$response['percakapan_id'] = $percakapan_id;
							$response['message'] = "Pesan berhasil dikirim kembali";
							$response['fmc'] = $fcm_token;

							// return json response
							return response()->json($response);
						}
					}

					

					
				}

				

			}
		
	}

	/*== Delete ==*/
	public function delete(Request $request) 
	{
		$percakapan_id = $request->percakapan_id;

		// get data
		$percakapan = Percakapan::find($percakapan_id);
		// theme checking
		if(empty($percakapan))
		{
			$response['status'] = "error";
			$response['message'] = "Percakapan not found";
			return response()->json($response);
			exit();
		}

		try
		{
			// delete
			Percakapan::find($percakapan_id)->delete();
		}
		catch(\Exception $e)
		{ 	
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the Percakapan";
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

	/*==  List Pesan ==*/
	public function list_pesan(Request $request, Pesan $pesan) 
	{	

		$percakapan_id 	= $request->percakapan_id;
		if(empty($percakapan_id)){
			//percakapan not found
			$response['status'] = "error";
			$response['message'] = "percakapan not found";
			return response()->json($response);
			exit();
		}else{
			//percakapan list
			$pesan = $pesan->get_list($percakapan_id);

			// response
			$response['status'] = "success";
			$response['message'] = "OK";
			$response['results'] = $pesan;
			
			// return json response
			return response()->json($response);
		}
		
	}


	/*== Send ==*/
	public function send(Request $request, Pesan $pesan, User $user, Percakapan $percakapan) 
	{

		$warga_id = $request->warga_id;
		$percakapan_id = $request->percakapan_id;
		$pesan_id = $request->pesan_id;
		$pesan_read = '3';
		$identity = $request->identity;

		$pesan = $pesan->get_detail_pesan($pesan_id, $percakapan_id);
		//
		$pesan_id = $pesan->pesan_id;
		$pesan = Pesan::find($pesan_id);
		$pesan->pesan_read = $pesan_read; // 0: jam 1: server 3:terkirim 4:read
		$pesan->save();

		// get token
		$user = $user->get_detail_warga($warga_id);
		$fcm_token = $user->fcm_token;

		// print_r($user);

		// //send to warga
		// $endpoint = "https://fcm.googleapis.com/fcm/send";
		// $client = new \GuzzleHttp\Client();
		// //

		// //create json data
		// $data_json = [
		// 			'notification' => [
		// 				'title' => 'Pesan status',
		// 				'body' => 'Pesan berhasil dikirim',
		// 					'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
		// 					'sound'	=> 'alarm.mp3'
		// 			],
		// 			'data' => [
		// 				'identity' => $identity,
		// 				'page' => 'pesan_update_status'
		// 			],
		// 				'to' => ''.$fcm_token.''
		// 	];

		// $requestAPI = $client->post( $endpoint, [
		// 			'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
		// 			'body' => json_encode($data_json)
		// ]);

		// response
		$response['status'] = "success";
		$response['message'] = "Pesan berhasil dikirim";
		$response['fmc'] = $fcm_token;

		// return json response
		return response()->json($response);
	}


}