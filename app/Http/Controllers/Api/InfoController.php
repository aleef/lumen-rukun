<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Info;
use App\Warga;
use App\Notifikasi;
use App\Wilayah;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use File;

class InfoController extends Controller
{

	/*==  List ==*/
	public function list(Request $request, Info $info)
	{

		$wil_id = $request->wil_id;
		$keyword = $request->keyword;
		$info_kat = $request->info_kat;
		$limit = $request->limit;
        $slider = $request->is_slider;

		// get data
		$info = $info->get_list($keyword, $wil_id, $info_kat, $limit);

		if(count($info) == 0 && $slider == 'Y')
		{
			$results = array(
				"info_id" => -999,
				"info_judul" => "Tambahkan Berita di Menu Informasi",
				"info_date" => "Beberapa saat yang lalu",
				"info_img" => url("/public/img/info")."/default.jpg"
			);

			$response['status'] = "success";
			$response['message'] = "OK";
			$response['results'] = array($results);
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $info;

		// return json response
		return response()->json($response);
	}

    public function list_limited(Request $request)
	{

		$wil_id = $request->wil_id;
		$keyword = $request->keyword;
		$info_kat = $request->info_kat;

        $page = empty($request->page) ? 1 : $request->page;
        $limit = empty($request->limit) ? 20 : $request->limit;

        $info = new Info;
		$list = $info->get_list_limited($keyword, $wil_id, $info_kat, $page, $limit);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $list;

		// return json response
		return response()->json($response);
	}

    public function list_undangan(Request $request, Info $info)
	{

		$wil_id = $request->wil_id;
		$keyword = $request->keyword;
        $type = $request->type;

        $page = empty($request->page) ? 1 : $request->page;
        $limit = empty($request->limit) ? 20 : $request->limit;

		// get data
		$listUndangan = $info->get_list_undangan($keyword, $wil_id, $type, $page, $limit);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $listUndangan;

		// return json response
		return response()->json($response);
	}

	/*==  Detail ==*/
	public function detail($id, Request $request, Info $info)
	{
		// get data
		$info = Info::find($id);
		if(empty($info))
		{
			$response['status'] = "error";
			$response['message'] = "Data tidak ditemukan";
            $response['results'] = [];
			return response()->json($response);
			exit();
		}

		if($info->info_img!='')
		{
			$info_img = URL('public/img/info/'.$info->info_img);
		} else {
			$info_img = URL('public/img/info/default.jpg');
		}

        $info_waktu = "Pukul ".$info->info_mulai." s.d ".(empty($info->info_akhir) ? "Selesai" : $info->info_akhir);

		$results = array(
			"info_date" => Carbon::parse($info->info_date)->isoFormat('D MMMM Y HH:mm'),
            "info_date_formatted" => Carbon::parse($info->info_date)->isoFormat('D MMMM Y'),
            "info_date_simple" => Carbon::parse($info->info_date)->format('Y-m-d'),
            "info_mulai" => $info->info_mulai,
            "info_akhir" => $info->info_akhir,
            "info_waktu" => $info_waktu,
			"info_judul" => $info->info_judul,
			"info_isi" => $info->info_isi,
			"info_img" => $info_img,
            "info_tempat" => $info->info_tempat,
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*== Add ==*/
	public function add(Request $request)
	{

        $response = array('status' => 'failed', 'message' => '');

		// account warga
		$wil_id = $request->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		$info_kat = $request->info_kat;
		$info_judul = $request->info_judul;
		$info_isi = $request->info_isi;
		$info_mulai = $request->info_mulai;
		$info_akhir = $request->info_akhir;
		$info_sts = ( $request->info_sts == '' || $request->info_sts == null || $request->info_sts == '0' ) ? 1 :  $request->info_sts;

		$warga_id = ( $request->warga_id == '' || $request->warga_id == null || $request->warga_id == '0' ) ? 0 : $request->warga_id;
        $info_date = $request->info_date; //untuk undangan
        $info_tempat = $request->info_tempat;

		$info = new Info;
		$warga = new Warga;

		// upload img
		if($request->file('info_img')!='')
		{
			// destination path
			$destination_path = public_path('img/info/');
			$img = $request->file('info_img');

			// upload
			$md5_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

            // resize photo
			$img = Image::make(URL("public/img/info/$md5_name.$ext"));
			$img->fit(1024,768);
			$img->save(public_path("img/info/$md5_name.$ext"));
			// set data
			$info->info_img = $img_file;

		}else{
			// set data
			$info->info_img = 'default.jpg';
		}

		//set data pengurus
		$info->wil_id = $wil_id;
		$info->info_kat = $info_kat;
		$info->info_judul = $info_judul;
		$info->info_isi = $info_isi;
		$info->info_mulai = $info_mulai;
		$info->info_akhir = $info_akhir;
		$info->info_sts = $info_sts;
        $info->info_tempat = $info_tempat;

        if($info_kat == '1') //bberita
		    $info->info_date = Carbon::now();
		else //undangan
            $info->info_date = Carbon::parse($info_date);

        $info->save();

		if($warga_id != 0) {
			$wargaList = $warga->get_all_warga_except_id($wil_id, $warga_id);
		} else {
			$wargaList = $warga->get_warga_not_pengurus_with_token($wil_id);
		}

		if(empty($wargaList))
		{
			// // response
			$response['status'] = "success";
			$response['message'] = "Warga tidak ditemukan";

		}else{

			//send to user peegurus
			$endpoint = "https://fcm.googleapis.com/fcm/send";
			$client = new \GuzzleHttp\Client();

			$title_arr = ['Info','Berita', 'Undangan'];
			$title = $title_arr[(int)$info_kat];
			$title = $title.' : '.$info_judul;
            $body = substr(strip_tags($info_isi),0,100)."...";

			foreach ($wargaList as $rows) {

                Notifikasi::create([
                    'warga_id' => $rows->warga_id,
                    'notif_title' => substr($title,0,100),
                    'notif_body' => $body,
                    'notif_page' => 'info',
                    'page_id' => $info->info_id,
                    'page_sts' => $info_kat,
                    'notif_date' => Carbon::now()
                ]);

				$fcm_token = $rows->fcm_token;

				//create json data
				$data_json = [
				        'notification' => [
				        	'title' => $title,
				        	'body' => $body,
				        	'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
				        	'sound'	=> 'alarm.mp3'
				        ],
						'data' => [
				        	'id' => ''.$info->info_id.'',
				        	'panic_tgl' => '',
				        	'panic_jam' => '',
				        	'panic_sts' => $info_kat,
				        	'page' => 'info'
				        ],
				        'to' => ''.$fcm_token.'',
						'collapse_key' => 'type_a',
				    ];

				$requestAPI = $client->post( $endpoint, [
			        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
			        'body' => json_encode($data_json)
			    ]);

			}
		}

		$results = array(
			"info_judul" => $info_judul,
			"warga" => $wargaList,
			"info_kat" => $info_kat
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
		// account warga
		$info_id = $request->info_id;
		$info_judul = $request->info_judul;
		$info_isi = $request->info_isi;
		$info_mulai = $request->info_mulai;
		$info_akhir = $request->info_akhir;
		$info_sts = $request->info_sts;
        $info_date = $request->info_date;
        $info_tempat = $request->info_tempat;


		$info = Info::find($info_id);

        $wil_id = $info->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		//set data pengurus
		$info->info_id = $info_id;
		$info->info_judul = $info_judul;
		$info->info_isi = $info_isi;
		$info->info_mulai = $info_mulai;
		$info->info_akhir = $info_akhir;
		$info->info_sts = $info_sts;
        $info->info_tempat = $info_tempat;

		if($request->file('info_img')!='')
		{
			// destination path
			$destination_path = public_path('img/info/');
			$img = $request->file('info_img');

			// upload
			$md5_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

            // resize photo
			$img = Image::make(URL("public/img/info/$md5_name.$ext"));
			$img->fit(1024,768);
			$img->save(public_path("img/info/$md5_name.$ext"));

            if($info->info_img != 'default.jpg')
                File::delete(public_path('img/info/').$info->info_img);

			// set data
			$info->info_img = $img_file;

		}

        if(!empty($info_date)) {
            $info->info_date = Carbon::parse($info_date);
        }

		$info->save();

		$results = array(
			"info_judul" => $info_judul,
			"info_isi" => $info_isi
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
		$info_id = $request->info_id;

		// get data
		$info = Info::find($info_id);

        $wil_id = $info->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		// theme checking
		if(empty($info))
		{
			$response['status'] = "error";
			$response['message'] = "Informasi with ID : $info_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
			if($info->info_img != 'default.jpg')
                File::delete(public_path('img/info/').$info->info_img);

			// delete
			Info::find($info_id)->delete();
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the Informasi";
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
