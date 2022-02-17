<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Response;
use File;
use Carbon\Carbon;
use App\Pengurus;
use App\Warga;
use App\Mk;
use App\User;
use App\Notifikasi;
use App\Wilayah;

class PengurusController extends Controller
{
	private $ctrl = 'pengurus';
	private $title = 'Pengurus';

	/*==  List Data ==*/
	public function list(Request $request, Pengurus $pengurus)
	{
		$wil_id = $request->wil_id;
		$mk_id = $request->mk_id;
		$mk_status = $request->mk_status;
		$keyword = $request->keyword;

		// get data
		$pengurus = $pengurus->get_list($wil_id, $mk_id, $keyword, $mk_status);
		if($pengurus->isEmpty())
		{
			$response['status'] = "error";
			$response['message'] = "Pengurus not found";
			return response()->json($response);
			exit();
		}

		// print_r($pengurus);
		$i=0;
		foreach ($pengurus as $row) {

			if($row->warga_foto!='')
			{
				$warga_foto = URL('public/img/pp/'.$row->warga_foto);
			} else {
				$warga_foto = URL('public/img/pp/default.png');
			}

			$result[$i]['pengurus_id'] = $row->pengurus_id;
			$result[$i]['updated_at'] = $row->updated_at;
			$result[$i]['mk_id'] = $row->mk_id;
			$result[$i]['warga_id'] = $row->warga_id;
			$result[$i]['pengurus_jabatan'] = $row->pengurus_jabatan;
			$result[$i]['warga_nama_depan'] = $row->warga_nama_depan;
			$result[$i]['warga_hp'] = $row->warga_hp;
			$result[$i]['warga_email'] = $row->warga_email;
			$result[$i]['warga_alamat'] = $row->warga_alamat;
			$result[$i]['warga_no_rumah'] = $row->warga_no_rumah;
			$result[$i]['warga_geo'] = $row->warga_geo;
			$result[$i]['wil_id'] = $row->wil_id;
			$result[$i]['warga_status'] = $row->warga_status;
			$result[$i]['warga_status_rumah'] = $row->warga_status_rumah;
			$result[$i]['kb_id'] = $row->kb_id;
			$result[$i]['warga_foto'] = $warga_foto;
			$result[$i]['warga_nama_belakang'] = $row->warga_nama_belakang;
			$result[$i]['wil_nama'] = $row->wil_nama;
			$result[$i]['mk_periode_mulai'] = $row->mk_periode_mulai;
			$result[$i]['mk_periode_akhir'] = $row->mk_periode_akhir;
			$result[$i]['mk_status'] = $row->mk_status;
			$result[$i]['mk_sk'] = $row->mk_sk;

		$i++;

		}


		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $result;

		// return json response
		return response()->json($response);
	}

	/*==  List Warga ==*/
	public function list_warga($wil_id, $mk_id, Request $request, Warga $warga)
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
		$warga = $warga->get_warga_not_pengurus($wil_id, $mk_id);

		if($warga->isEmpty())
		{
			$response['status'] = "error";
			$response['message'] = "Warga not found";
			return response()->json($response);
			exit();
		}else{

			$response['status'] = "success";
			$response['message'] = "OK";
			$response['results'] = $warga;

			//return json response
			return response()->json($response);
		}

	}

	/*==  Detail ==*/
	public function detail($pengurus_id, Request $request, Pengurus $pengurus)
	{

		// get data
		$pengurus = $pengurus->get_detail($pengurus_id);
		if(empty($pengurus))
		{
			$response['status'] = "error";

			$response['message'] = "Pengurus not found";
			return response()->json($response);
			exit();
		}

		if($pengurus->warga_foto!='')
		{
			$warga_foto = URL('public/img/pp/'.$pengurus->warga_foto);
		} else {
			$warga_foto = URL('public/img/pp/default.png');
		}

		$results = array(
			"pengurus_id" => $pengurus->pengurus_id,
			"pengurus_jabatan" => $pengurus->pengurus_jabatan,
			"warga_nama_depan" => $pengurus->warga_nama_depan,
			"warga_nama_belakang" => $pengurus->warga_nama_belakang,
			"warga_hp" => $pengurus->warga_hp,
			"warga_id" => $pengurus->warga_id,
			"warga_foto" => $warga_foto,
			"mk_id" => $pengurus->mk_id
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);

	}

	/*== Sign Up ==*/
	public function signup(Request $request, User $user, Pengurus $pengurus)
	{
		// account warga
		$warga_id = $request->warga_id;
		$pengurus_jabatan = $request->pengurus_jabatan;
		//$pengurus_foto = $request->pengurus_foto;
		$mk_id = $request->mk_id;

		//validasi nama pengurus jabatan
		$results = $pengurus->get_detail_jabatan($mk_id, $pengurus_jabatan);

		if($results->nama_jabatan == 1){

			// response
			$response['status'] = "failed";
			$response['message'] = "Nama Jabatan Sama";

			// return json response
			return response()->json($response);

		}else{

			// validate param
			if($pengurus_jabatan=='')
			{
				$response['status'] = "error";
				$response['message'] = "Jabatan are required fields";
				return response()->json($response);
				exit();
			}

			$user = new User;
			$pengurus = new Pengurus;

			//set data pengurus
			$pengurus->warga_id = $warga_id;
			$pengurus->pengurus_jabatan = $pengurus_jabatan;
			$pengurus->updated_at = Carbon::now();
			$pengurus->mk_id = $mk_id;
			$pengurus->save();

			//update status warga yang menjadi pengurus
			$user_ = $user->get_detail_warga($warga_id);
			$user_id = $user_->user_id;
			$fcm_token = $user_->fcm_token;
			 // pengurus
			//$pengurus->pengurus_foto = $pengurus_foto;

			$user = User::find($user_id);
			$user->user_id = $user_id;
			$user->user_type = 2;
			$user->user_status = 0;
			$user->save();

			// print_r($user_id);
			// print_r($user);

			//
				$title = 'Status Kepengurusan';
				$body = 'Status Kepengurusan Anda sudah dinonaktifkan';

				//kirim notifikasi kalau pengurus sudah dihapus
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

					$fcm_token = $fcm_token;

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

			$results = array(
				"pengurus_jabatan" => $pengurus_jabatan
			);

			// response
			$response['status'] = "success";
			$response['message'] = "OK";
			$response['results'] = $results;

			// return json response
			return response()->json($response);

		}


	}

	/*== Update ==*/
	public function update(Request $request, Pengurus $pengurus)
	{
		// account warga
		$mk_id = $request->mk_id;
		$pengurus_id = $request->pengurus_id;
		$pengurus_jabatan = $request->pengurus_jabatan;
		//$pengurus_foto = $request->pengurus_foto;

        $mk = Mk::find($mk_id);

        $wil_id = $mk->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		//validasi nama pengurus jabatan
		$results = $pengurus->get_detail_jabatan($mk_id, $pengurus_jabatan);

		if($results->nama_jabatan == 1){

			// response
			$response['status'] = "failed";
			$response['message'] = "Nama Jabatan Sama";

			// return json response
			return response()->json($response);

		}else{

			$pengurus = Pengurus::find($pengurus_id);

			if(empty($pengurus))
			{
				$response['status'] = "error";
				$response['message'] = "Pengurus not found";
				return response()->json($response);
				exit();
			}
		}

		//set data pengurus
		$pengurus->pengurus_jabatan = $pengurus_jabatan;
		$pengurus->updated_at = Carbon::now();
		$pengurus->save();

		$results = array(
			"pengurus_jabatan" => $pengurus_jabatan
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*== SK ==*/
	public function sk(Request $request)
	{
		$mk_id = $request->mk_id;
		// upload sk
		if($request->file('mk_sk')!='')
		{

			$mk = Mk::find($mk_id);

            $wil_id = $mk->wil_id;
            $response = array('status' => 'failed', 'message' => '');
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }


			//delete before
			if(!empty($mk->mk_sk)){
				File::delete(public_path('pengurus/sk/').$mk->mk_sk);
			}

			// destination path
			$destination_path = public_path('pengurus/sk/');
			$sk = $request->file('mk_sk');

			// upload
			$md5_name = uniqid()."_".md5_file($sk->getRealPath());
			$ext = $sk->getClientOriginalExtension();
			$sk->move($destination_path,"$md5_name.$ext");
			$sk_file = "$md5_name.$ext";


			// set data
			$mk->mk_sk = $sk_file;
			$mk->save();


			$results = array(
				"mk_id" => $mk_id
			);

			// response
			$response['status'] = "success";
			$response['message'] = "OK";
			$response['results'] = $results;

			// return json response
			return response()->json($response);
		}
	}

	/*== Delete ==*/
	public function delete(Request $request, User $user, Warga $warga)
	{
		$pengurus_id = $request->pengurus_id;

		// get data
		$pengurus = Pengurus::find($pengurus_id);
		// theme checking
		if(empty($pengurus))
		{
			$response['status'] = "error";
			$response['message'] = "Pengurus with ID : $pengurus_id not found";
			return response()->json($response);
			exit();
		}

        $mk = Mk::find($pengurus->mk_id);
        //cek subscription
        $wil_id = $mk->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }


		$warga_id = $pengurus->warga_id;
		$warga = $warga->get_pengurus_by_warga($warga_id);
		if(empty($warga))
		{
			$response['status'] = "error";
			$response['message'] = "Warga with ID : $warga_id not found";
			return response()->json($response);
			exit();

		}else{

			try
			{
				// delete
				//update status pengurus menjadi warga jika dinonaktifkan
				$user_id = $warga->user_id;
				$fcm_token = $warga->fcm_token;

				//update pengurus ke warga
				$user = User::find($user_id);
				$user->user_type = 3;
				$user->save();

				//
				$title = 'Status Kepengurusan';
				$body = 'Status Kepengurusan Anda sudah dinonaktifkan';

				//kirim notifikasi kalau pengurus sudah dihapus
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

					$fcm_token = $fcm_token;

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

				    Pengurus::find($pengurus_id)->delete();

			}
			catch(\Exception $e)
			{
				// failed
				$response['status'] = "error";
				$response['message'] = "Error, can't delete the Pengurus ".$e." ";
				return response()->json($response);
				exit();
			}

			$warga_id = $pengurus->warga_id;

			$user_ = $user->get_detail_warga($warga_id);
			$user_id = $user_->user_id;
			//
			$user = User::find($user_id);
			$user->user_id = $user_id;
			$user->user_type = 3;
			$user->save();

			// response
			$response['status'] = "success";
			$response['message'] = "OK";
			//$response['results'] = $results;

			// return json response
			return response()->json($response);
		}
	}

	/*==  List Admin ==*/
	public function list_admin(Request $request, Pengurus $pengurus)
	{
		$wil_id = $request->wil_id;
		// get data
		$pengurus = $pengurus->get_list_admin($wil_id);

		if($pengurus->isEmpty())
		{
			$response['status'] = "error";
			$response['message'] = "Admin not found";
			return response()->json($response);
			exit();
		}else{

			$i=0;
			foreach ($pengurus as $row) {

				if($row->warga_foto!='')
				{
					$warga_foto = URL('public/img/pp/'.$row->warga_foto);
				} else {
					$warga_foto = URL('public/img/pp/default.png');
				}

				$result[$i]['pengurus_id'] = $row->pengurus_id;
				$result[$i]['updated_at'] = $row->updated_at;
				$result[$i]['mk_id'] = $row->mk_id;
				$result[$i]['warga_id'] = $row->warga_id;
				$result[$i]['pengurus_jabatan'] = $row->pengurus_jabatan;
				$result[$i]['warga_nama_depan'] = $row->warga_nama_depan;
				$result[$i]['warga_hp'] = $row->warga_hp;
				$result[$i]['warga_email'] = $row->warga_email;
				$result[$i]['warga_alamat'] = $row->warga_alamat;
				$result[$i]['warga_no_rumah'] = $row->warga_no_rumah;
				$result[$i]['warga_geo'] = $row->warga_geo;
				$result[$i]['wil_id'] = $row->wil_id;
				$result[$i]['warga_status'] = $row->warga_status;
				$result[$i]['warga_status_rumah'] = $row->warga_status_rumah;
				$result[$i]['kb_id'] = $row->kb_id;
				$result[$i]['warga_foto'] = $warga_foto;
				$result[$i]['warga_nama_belakang'] = $row->warga_nama_belakang;
				$result[$i]['mk_periode_mulai'] = $row->mk_periode_mulai;
				$result[$i]['mk_periode_akhir'] = $row->mk_periode_akhir;
				$result[$i]['mk_status'] = $row->mk_status;
				$result[$i]['mk_sk'] = $row->mk_sk;
				$result[$i]['user_type'] = $row->user_type;
				$result[$i]['user_status'] = $row->user_status;

			$i++;

			}

			$response['status'] = "success";
			$response['message'] = "OK";
			$response['results'] = $result;

			//return json response
			return response()->json($response);
		}

	}

	/*==  List Pengurus ==*/
	public function list_pengurus_active(Request $request, Pengurus $pengurus)
	{
		$wil_id = $request->wil_id;
		// get data
		$pengurus = $pengurus->get_pengurus_active($wil_id);

		if($pengurus->isEmpty())
		{
			$response['status'] = "error";
			$response['message'] = "pengurus not found";
			return response()->json($response);
			exit();
		}else{

			$response['status'] = "success";
			$response['message'] = "OK";
			$response['results'] = $pengurus;

			//return json response
			return response()->json($response);
		}

	}

	/*== Admin add ==*/
	public function add_admin(Request $request, User $user, Pengurus $pengurus)
	{
		$warga_id = $request->warga_id;

		//update status warga yang menjadi pengurus
		$user_ = $user->get_detail_warga($warga_id);
		$user_id = $user_->user_id;
		$fcm_token = $user_->fcm_token;


		$user = User::find($user_id);
		$user->user_id = $user_id;
		$user->user_type = 2;
		$user->user_status = 1;
		$user->save();

				$title = 'Status Admin';
				$body = 'Anda sudah dijadikan Admin';

				//kirim notifikasi kalau pengurus sudah dihapus
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

					$fcm_token = $fcm_token;

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
					        	'page' => 'admin'
					        ],
					        'to' => ''.$fcm_token.'',
							'collapse_key' => 'type_a',
					    ];

					$requestAPI = $client->post( $endpoint, [
				        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
				        'body' => json_encode($data_json)
				    ]);

			// response
			$response['status'] = "success";
			$response['message'] = "OK";

			// return json response
			return response()->json($response);
	}

	/*== Admin delete ==*/
	public function delete_admin(Request $request, User $user, Pengurus $pengurus)
	{
		$warga_id = $request->warga_id;
		$wil_id = $request->wil_id;

		//update status warga yang menjadi pengurus
		$user_ = $user->get_detail_warga($warga_id);
		$user_id = $user_->user_id;
		$fcm_token = $user_->fcm_token;

		//cek jumlah admin
		$admin_ = $pengurus->get_count_admin($wil_id);
		$c_admin = $admin_->c_admin;
		if($c_admin == 1){

			// response
			$response['status'] = "error";
			$response['message'] = "Pengurus minimal ada 1";

			// return json response
			return response()->json($response);

		}else{

			$user = User::find($user_id);
			$user->user_id = $user_id;
			$user->user_type = 2;
			$user->user_status = 0;
			$user->save();

				$title = 'Status Admin';
				$body = 'Anda sudah dinonaktifkan menjadi Admin';

				//kirim notifikasi kalau pengurus sudah dihapus
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

					$fcm_token = $fcm_token;

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
					        	'page' => 'admin'
					        ],
					        'to' => ''.$fcm_token.'',
							'collapse_key' => 'type_a',
					    ];

					$requestAPI = $client->post( $endpoint, [
				        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
				        'body' => json_encode($data_json)
				    ]);

			// response
			$response['status'] = "success";
			$response['message'] = "OK";

			// return json response
			return response()->json($response);
		}
	}
	public function detailcrm($pengurus_id, Request $request, Pengurus $pen)
	{
		// validate param
		if ($pengurus_id == '') {
			$response['status'] = "error";
			$response['message'] = "pengurus_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$peng = $pen->get_detail($pengurus_id);
		// $warga = Info::find($warga_id);
		if (empty($peng)) {
			$response['status'] = "error";
			$response['message'] = "Pengurus not found";
			return response()->json($response);
			exit();
		}



		$results = array(
			"warga_id" => $peng->warga_id,
			"pengurus_id" => $peng->pengurus_id,
			"pengurus_jabatan" => $peng->pengurus_jabatan,
			"mk_id" => $peng->mk_id,
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}
}
