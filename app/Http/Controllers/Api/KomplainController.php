<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Komplain;
use App\Notifikasi;
use App\Pengurus;
use App\Kk;
use App\Warga;
use App\Wilayah;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use File;


class KomplainController extends Controller
{

	/*==  List ==*/
	public function list(Request $request, Komplain $komplain)
	{
		$wil_id = $request->wil_id;
		$warga_id = $request->warga_id;
		$status_pp = $request->status_pp;

		$keyword = $request->keyword;

		// get data
		$komplain = $komplain->get_list($warga_id, $wil_id, $status_pp, $keyword);
        $data = array();
        $i = 0;
        foreach($komplain as $item) {
            $data[$i] = json_decode(json_encode($item), true);
            $data[$i]['foto_url'] = url("/public/img/komplain/".$item->komp_foto);
            $data[$i]['created_date_formatted'] = Carbon::parse($item->create_date)->isoFormat("D MMMM Y HH:mm");
            $data[$i]['komp_isi_clip'] = substr($item->komp_isi, 0, 50)."...";
            $i++;

        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $data;

		// return json response
		return response()->json($response);
	}

    /*==  List ==*/
	public function list_limited(Request $request)
	{
		$wil_id = $request->wil_id;
		$warga_id = $request->warga_id;
		$status_pp = $request->status_pp;

		$keyword = $request->keyword;

        $page = empty($request->page) ? 1 : $request->page;
        $limit = empty($request->limit) ? 20 : $request->limit;

        $komplain = new Komplain;
		// get data
		$list = $komplain->get_list_limited($warga_id, $wil_id, $status_pp, $keyword, $page, $limit);
        $data = array();
        $i = 0;
        foreach($list as $item) {
            $data[$i] = json_decode(json_encode($item), true);
            $data[$i]['foto_url'] = url("/public/img/komplain/".$item->komp_foto);
            $data[$i]['created_date_formatted'] = Carbon::parse($item->create_date)->isoFormat("D MMMM Y HH:mm");
            $data[$i]['komp_isi_clip'] = substr($item->komp_isi, 0, 50)."...";
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
	public function detail($id)
	{
		// get data
		$komplain = Komplain::find($id);
		if(empty($komplain))
		{
			$response['status'] = "error";
			$response['message'] = "Komplain tidak ditemukan";
			return response()->json($response);
			exit();
		}

        $warga = Warga::find($komplain->warga_id);
        $totalKomentar = Kk::where('komp_id', $id)->count();

        $statusPP = ['1' => 'Publik', '2' => 'Privat'];
        $status = ['Belum Ditanggapi', 'Sedang Diproses', 'Selesai'];

        $komplain->warga_nama_depan = $warga->warga_nama_depan;
        $komplain->warga_nama_belakang = $warga->warga_nama_belakang;
        $komplain->foto_url = url("/public/img/komplain/".$komplain->komp_foto);
        $komplain->created_date_formatted =  Carbon::parse($komplain->create_date)->isoFormat("D MMMM Y HH:mm");
		$komplain->status_pp = $statusPP[$komplain->komp_status_pp];
        $komplain->status = $status[$komplain->komp_status];
        $komplain->total_komentar = $totalKomentar;

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $komplain;

		// return json response
		return response()->json($response);
	}


	public function addWithImage(Request $request)
	{

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

		// account warga
		$warga_id = $request->warga_id;
		$wil_id = $request->wil_id;
		$komp_judul = $request->komp_judul;
		$komp_isi = $request->komp_isi;
		$komp_status = ($request->komp_status == '') ? '0' : $request->komp_status;
        $komp_status_pp = ($request->komp_status_pp == '') ? '1' : $request->komp_status_pp;

        //komp_status --> 0 : Belum Ditanggapi , 1 : Sedang Diproses, 2 : Sudah Selesai
        //komp_status_pp --> 1 : Publik , 2 : Private

        //cek subscription
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		$komplain = new Komplain;
		$pengurus = new Pengurus;

		// upload img
		if($request->file('komp_foto')!='')
		{
			// destination path
			$destination_path = public_path('img/komplain/');
			$img = $request->file('komp_foto');

			// upload
			$file_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$file_name.$ext");
			$img_file = "$file_name.$ext";

            $img = Image::make(URL("public/img/komplain/$file_name.$ext"));
			$img->fit(1024,768);
			$img->save(public_path("img/komplain/$file_name.$ext"));

			// set data
			$komplain->komp_foto = $img_file;

		}else{
			// set data
			$komplain->komp_foto = 'default.jpg';
		}

		//set data pengurus
		$komplain->warga_id = $warga_id;
		$komplain->wil_id = $wil_id;
		$komplain->komp_judul = $komp_judul;
		$komplain->komp_isi = $komp_isi;
		$komplain->komp_status = $komp_status;
		$komplain->komp_status_pp = $komp_status_pp;
		$komplain->create_date = Carbon::now();
		$komplain->save();

		$pengurusList = $pengurus->get_list_with_token($wil_id);

		if(empty($pengurusList))
		{
			// // response
			$response['status'] = "failed";
			$response['message'] = "Pengurus belum dientry";

		}else{

			//send to user peegurus
			$endpoint = "https://fcm.googleapis.com/fcm/send";
			$client = new \GuzzleHttp\Client();

			foreach ($pengurusList as $rows) {

				$fcm_token = $rows->fcm_token;

                $title = 'Pengaduan : '.$komp_judul;
                $body = substr($komp_isi,0,100)."...";


                Notifikasi::create([
                    'warga_id' => $rows->warga_id,
                    'notif_title' => substr($title,0,100),
                    'notif_body' => substr($body,0,255),
                    'notif_page' => 'komplain_detil',
                    'page_id' => $komplain->komp_id,
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
				        	'id' => ''.$komplain->komp_id.'',
				        	'panic_tgl' => '',
				        	'panic_jam' => '',
				        	'panic_sts' => '',
				        	'page' => 'komplain_detil'
				        ],
				        'to' => ''.$fcm_token.'',
						'collapse_key' => 'type_a',
				    ];

				$requestAPI = $client->post( $endpoint, [
			        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
			        'body' => json_encode($data_json)
			    ]);

			}


			// response
			$response['status'] = "success";
			$response['message'] = "OK";

		}

		// return json response
		return response()->json($response);
	}

	/*== Update ==*/
	public function updateWithImage(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

		// account warga
		$komp_id = $request->komp_id;
		$komp_judul = $request->komp_judul;
		$komp_isi = $request->komp_isi;

		$komplain = Komplain::find($komp_id);

        //cek subscription
        $wil_id = $komplain->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        // upload img
		if($request->file('komp_foto')!='')
		{
			// destination path
			$destination_path = public_path('img/komplain/');
			$img = $request->file('komp_foto');

			// upload
			$file_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$file_name.$ext");
			$img_file = "$file_name.$ext";

            $img = Image::make(URL("public/img/komplain/$file_name.$ext"));
			$img->fit(1024,768);
			$img->save(public_path("img/komplain/$file_name.$ext"));

            if($komplain->komp_foto != 'default.jpg')
                File::delete(public_path('img/komplain/').$komplain->komp_foto);
			// set data
			$komplain->komp_foto = $img_file;
		}

		//set data pengurus
		$komplain->komp_judul = $komp_judul;
        $komplain->komp_isi = $komp_isi;
		$komplain->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}

	/*== Update ==*/
	public function update_status(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

		// account warga
		$komp_id = $request->komp_id;
		$komp_status = $request->komp_status;

		//validate param
		if($komp_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "ID tidak tersedia";
			return response()->json($response);
			exit();
		}

		$komplain = Komplain::find($komp_id);

        //cek subscription
        $wil_id = $komplain->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		//set data pengurus
		$komplain->komp_status = $komp_status;
		$komplain->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";


		// return json response
		return response()->json($response);
	}

    /*== Update ==*/
	public function update_status_pp(Request $request)
	{

		$komp_id = $request->komp_id;
		$komplain = Komplain::find($komp_id);

		$komplain->komp_status_pp = ($komplain->komp_status_pp == '1') ? '2' : '1'; //private publik
		$komplain->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}


    /*== Update ==*/
	public function delete(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

		// account warga
		$komp_id = $request->komp_id;
		$komplain = Komplain::find($komp_id);

        try
		{
            $wil_id = $komplain->wil_id;
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }

			if($komplain->komp_foto != 'default.jpg')
                File::delete(public_path('img/komplain/').$komplain->info_img);

            Kk::where('komp_id',$komp_id)->delete();
            $komplain->delete();
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Delete komplain gagal";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}
}
