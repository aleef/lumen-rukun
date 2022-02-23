<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\DB;
use DateTime;
use Response;
use File;
use App\Notifikasi;
use App\PaketLangganan;
use App\Wilayah;
use App\Pengurus;
use App\Warga;
use App\Keuangan;
use App\WargaUndang;
use App\Tarif;
use App\Phonebook;
use App\Info;
use App\Peraturan;
use App\Billing;
use App\UbahStatus;
use Carbon\Carbon;
use App\GlobalVariable;

class WilayahController extends Controller
{
	/*==  Detail Data ==*/
	public function detail(Request $request, Wilayah $wilayah)
	{

		// get data
		$this_month = date('m');
		$last_month = date("m", strtotime("-1 month"));
		$wil_id = $request->wil_id;
		$wilayah = $wilayah->get_detail_by_wil($wil_id, $this_month, $last_month);


		if(empty($wilayah))
		{
			$response['status'] = "error";
			$response['message'] = "wilayah not found";
			return response()->json($response);
			exit();
		}
		// //
		if($wilayah->wil_foto!='')
		{
			$wil_foto = URL('public/img/wilayah/'.$wilayah->wil_foto);
		} else {
			$wil_foto = URL('public/img/wilayah/default.jpg');
		}

		// if($wilayah->total_tagihan!='')
		// {
		// 	$_total_tagihan = $wilayah->total_tagihan;
		// } else {
		// 	$_total_tagihan = '0';
		// }

		// if($wilayah->total_saldo!='')
		// {
		// 	$_total_saldo = $wilayah->total_saldo;
		// } else {
		// 	$_total_saldo = '0';
		// }

		// if($wilayah->total_masuk!='')
		// {
		// 	$_total_masuk = $wilayah->total_masuk;
		// } else {
		// 	$_total_masuk = '0';
		// }

		// if($wilayah->total_keluar!='')
		// {
		// 	$_total_keluar = $wilayah->total_keluar;
		// } else {
		// 	$_total_keluar = '0';
		// }

		//1:apartemen,2:komplek/cluster,3:perumahan/perkampungan,4:paguyuban

		switch ($wilayah->wil_jenis) {
			case '1':
				$wilayahJenisNAme = 'Apartemen';
				break;

			case '2':
				$wilayahJenisNAme = 'Komplek/Cluster';
				break;

			case '3':
				$wilayahJenisNAme = 'Perumahan/Perkampungan';
				break;

			case '4':
				$wilayahJenisNAme = 'Paguyuban';
				break;
		}

		$results = array(
			"wil_id" => $wilayah->wil_id,
			"wil_nama" => $wilayah->wil_nama,
			"wil_alamat" => $wilayah->wil_alamat,
			"wil_kab" => $wilayah->kabkota_nama,
			"wil_kec" => $wilayah->kec_nama,
			"wil_kel" => $wilayah->kel_nama,
			"wil_kode" => $wilayah->wil_kode,
			"wil_geolocation" => $wilayah->wil_geolocation,
			"wil_foto" => $wil_foto,
			"wil_jenis" => $wilayahJenisNAme,
			//
			// "total_undang_warga" => $wilayah->total_undang_warga,
			// "total_warga" => $wilayah->total_warga,
			// "total_komplain" => $wilayah->total_komplain,
			// "total_tagihan" => $_total_tagihan,
			// "total_warga_belum_bayar" => $wilayah->total_warga_belum_bayar,
			// //
			// "total_saldo" => $_total_saldo,
			// "total_masuk" => $_total_masuk,
			// "total_keluar" => $_total_keluar,


		);

		//response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);

	}


		/*==  Detail ==*/
	public function detailcrm($wil_id, Request $request)
		{
			// get data
			$info = Wilayah::find($wil_id);
			/*$info =DB::table('wilayah as a')
			->join('warga as b','b.wil_id','=','a.wil_id')
			->join('pengurus as c','c.warga_id','=','b.warga_id')
			->where('a.wil_id',$id)
			->select('a.*','b.*', 'c.pengurus_jabatan')
			->get();*/
			/*if(empty($info))
			{
				$response['status'] = "error";
				$response['message'] = "Informasi not found";
				$response['results'] = [];
				return response()->json($response);
				exit();
			}


			$results = array(
				"sales_nama" => $mar->sales_nama,
				"sales_hp" => $mar->sales_hp,
				"sales_email" => $mar->sales_email,
				"sales_kode" => $mar->sales_kode,
				"sales_parent_id" => $mar->sales_parent_id,
				"sales_head" => $mar->sales_head,
			);

			$response['status'] = "success";
			$response['message'] = "OK";
			$response['results'] = $results;*/

			// return json response
			//return response()->json($response);
			return $info;
	}
	public function detailadmin($wil_id)
	{
			// get data
			$info =DB::table('warga as b')
			->join('pengurus as c','c.warga_id','=','b.warga_id')
			->where('b.wil_id',$wil_id)
			->select('b.*', 'c.pengurus_jabatan', 'c.pengurus_id')
			->first();
			//return $info;
			return response()->json($info);
	}
	public function list_pengurus($wil_id,Request $request)
	{

		$draw = $request->get('draw');
        $start = $request->get("start");
        $length = $request->get("length");
        $search = $request->get('search')['value'];

        $order =  $request->get('order');

         $col = 0;
         $dir = "desc";

         if(!empty($order)) {
             foreach($order as $o) {
                 $col = $o['column'];
                 $dir= $o['dir'];
             }
        }

         $columns_valid = array("warga_id", "warga_nama_depan", "warga_alamat", "warga_hp", "warga_email", "warga_no_rumah", "pengurus_jabatan");
         if(!isset($columns_valid[$col])) {
            $order = 'null';
        } else {
            $order = $columns_valid[$col];
        }

		$res = DB::table('warga as b')
			->join('pengurus as c','c.warga_id','=','b.warga_id')
			->where('b.wil_id',$wil_id)
			->select('b.*', 'c.pengurus_jabatan', 'c.pengurus_id');

		if($search!=''){
			$res =  $res->where('a.warga_nama_depan', 'ILIKE', '%'.$search.'%')->orWhere('a.warga_nama_belakang', 'ILIKE', '%'.$search.'%');
		}
		if(isset($order)){
				$res = $res->orderBy($order, $dir);
		}else{
				$order = $res->orderBy('a.warga_id', 'desc');
		}
		if(isset($length) || isset($start)){
				$res = $res->skip($start)->take($length);
		}
		$res = $res->get();
        $i = 1;
        $data = array();
		if(!empty($res) || $res !=''){

			foreach($res as $r) {

				$data[] = array(
					$start + $i,
					$r->warga_nama_depan.' '.$r->warga_nama_belakang,
					$r->warga_alamat,
					$r->warga_hp,
					$r->warga_email,
					$r->warga_no_rumah,
					$r->pengurus_jabatan,
					'<form action="warga/'.$r->pengurus_id.'/destroy" method="POST"> <a href="#edit-pen" onclick="showPen('.$r->pengurus_id.')" id="edit-pen-btn" data-id="'.$r->pengurus_id.'"><i class="fa fa-edit fa-lg text-success" title="Edit"></i></a> <a href="#" id="hapus" data-id="'.$r->pengurus_id.'" data-nama="'.$r->warga_nama_depan.'"><i class="fa fa-trash fa-lg text-danger" title="Hapus"></a></form>'
				);
				$i++;
			}
			//total data lead
		   $total_sal = DB::table('warga as b')
		   ->join('pengurus as c','c.warga_id','=','b.warga_id')
		   ->where('b.wil_id',$wil_id)->get()->count();
		   //total filtered
		   $total_fil = DB::table('warga as b')
		   ->join('pengurus as c','c.warga_id','=','b.warga_id')
		   ->where('b.wil_id',$wil_id);
		   //$total_fil = $total_fil->groupBy('b.wil_id');
		   if($search!=''){
				$total_fil = $total_fil->where('a.warga_nama_depan', 'ILIKE', '%'.$search.'%')->orWhere('a.warga_nama_belakang', 'ILIKE', '%'.$search.'%');
			}
			$total_fil = $total_fil->get()->count();

			$output = array(
				"draw" => $draw,
				"recordsTotal" => $total_sal,
				"recordsFiltered" => $total_fil,
				"data" => $data
			);
		}else{
			$output = array(
				"draw" => $draw,
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => $data
			);
		}

         // return json response
         return response()->json($output, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
	}

	/*== Update Foto ==*/
	public function update_foto(Request $request)
	{
		$wil_id = $request->wil_id;
		$wil_foto = $request->file('wil_foto');

        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		//
		$wilayah = Wilayah::find($wil_id);
		//
		$warga_foto__ = $wilayah->wil_foto;


		// upload img
		if($wil_foto!='')
		{
			// destination path
			$destination_path = public_path('img/wilayah/');
			$img = $wil_foto;

			// upload
			$md5_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

			// resize photo
			$img = Image::make(URL("public/img/wilayah/$md5_name.$ext"));
			$img->fit(1024,768);
			$img->save(public_path("img/wilayah/$md5_name.$ext"));

            if($wilayah->wil_foto != 'default.jpg')
		        File::delete(public_path('img/wilayah/').$warga_foto__);
			// set data
			$wilayah->wil_foto = $img_file;

		}else{
			// set data
			$wilayah->wil_foto = 'default.jpg';
		}

		// save
		$wilayah->save();
		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}

    public function update_logo(Request $request)
	{
		$wil_id = $request->wil_id;
		$wil_logo = $request->file('wil_logo');

        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }


		//
		$wilayah = Wilayah::find($wil_id);
		//
		$logoWilayah = $wilayah->wil_logo;


		// upload img
		if($wil_logo!='')
		{
			// destination path
			$destination_path = public_path('img/logo_wilayah/');
			$img = $wil_logo;

			// upload
			$md5_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

			// resize photo
			$img = Image::make(URL("public/img/logo_wilayah/$md5_name.$ext"));
			$img->fit(100);
			$img->save(public_path("img/logo_wilayah/$md5_name.$ext"));

            if($wilayah->wil_logo != 'default.png')
                File::delete(public_path('img/logo_wilayah/').$logoWilayah);

			// set data
			$wilayah->wil_logo = $img_file;

            // save
            $wilayah->save();
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
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

		$wil_id = $request->wil_id;

        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }


		$wil_nama = $request->wil_nama;
		$wil_alamat = $request->wil_alamat;
        $wil_tag_due = $request->wil_tag_due;

		$wilayah = Wilayah::find($wil_id);
        $wilayah->wil_id = $wil_id;

		//
		if($wil_nama!='')
			$wilayah->wil_nama = $wil_nama;
		if($wil_alamat!='')
			$wilayah->wil_alamat = $wil_alamat;
		if($wil_tag_due != '')
            $wilayah->wil_tag_due = $wil_tag_due;

		// save
		$wilayah->save();

		$results = array(
			"wil_nama" => $wilayah->wil_nama,
		);
		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;


		// return json response
		return response()->json($response);
	}

    public function update_rekening(Request $request)
	{
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

		$wil_id = $request->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		$wil_rek_no = $request->wil_rek_no;
		$wil_rek_bank_tujuan = $request->wil_rek_bank_tujuan;
        $wil_rek_atas_nama = $request->wil_rek_atas_nama;

		$wilayah = Wilayah::find($wil_id);
        $wilayah->wil_id = $wil_id;

		if($wil_rek_no!='')
			$wilayah->wil_rek_no = $wil_rek_no;
		if($wil_rek_bank_tujuan!='')
			$wilayah->wil_rek_bank_tujuan = $wil_rek_bank_tujuan;
		if($wil_rek_atas_nama != '')
            $wilayah->wil_rek_atas_nama = $wil_rek_atas_nama;

		// save
		$wilayah->save();

		$results = array(
			"wil_rek_no" => $wilayah->wil_rek_no,
		);
		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*== Update ==*/
	public function update_crm(Request $request)
	{
		$wil_id = $request->wil_id;

		$wil_nama = $request->wil_nama;
		$wil_alamat = $request->wil_alamat;
		$wil_jenis = $request->wil_jenis;
		$wil_rt = $request->wil_rt;
		$wil_rw = $request->wil_rw;
		$kel_id = $request->kel_id;
		$old_foto = $request->old_foto;
		$old_logo = $request->old_logo;
		$wil_foto = $request->file('wil_foto');
		$wil_logo = $request->file('wil_logo');

		$wilayah = Wilayah::find($wil_id);

		$wilayah->wil_nama = $wil_nama;
		$wilayah->wil_alamat = $wil_alamat;
		$wilayah->wil_jenis = $wil_jenis;
		$wilayah->wil_rt = $wil_rt;
		$wilayah->wil_rw = $wil_rw;
		$wilayah->kel_id = $kel_id;

		// upload img
		if($wil_foto!='')
		{
			// destination path
			$destination_path = base_path()."/public/img/wilayah/";
			$img = $wil_foto;

			// upload
			$ext = $img->getClientOriginalExtension();
			$md5_name = "foto-" . uniqid() . "_" . md5_file($img->getRealPath()).".".$ext;
			$request->file('wil_foto')->move($destination_path, $md5_name);
			$img_file = "$md5_name";
			//$img->move($destination_path,"$md5_name.$ext");

			// resize photo
			//$img = Image::make(URL("public/img/wilayah/$md5_name.$ext"));
			//$img->fit(500);
			//$img->save(base_path()."img/wilayah/$md5_name.$ext");

			// set data
			$wilayah->wil_foto = $img_file;

		}else{
			$wilayah->wil_foto = $old_foto;
		}
		if($wil_logo!='')
		{
			// destination path
			// $destination_path = public_path('img/logo_wilayah/');
			// $img = $wil_logo;

			// // upload
			// $md5_name = uniqid()."_".md5_file($img->getRealPath());
			// $ext = $img->getClientOriginalExtension();
			// $img->move($destination_path,"$md5_name.$ext");
			// $img_file = "$md5_name.$ext";

			// // resize photo
			// $img = Image::make(URL("public/img/logo_wilayah/$md5_name.$ext"));
			// $img->fit(500);
			// $img->save(public_path("img/logo_wilayah/$md5_name.$ext"));
			$destination_path = base_path() . "/public/img/logo_wilayah/";
			$img = $wil_logo;

			// upload
			$ext = $img->getClientOriginalExtension();
			$md5_name = "foto-" . uniqid() . "_" . md5_file($img->getRealPath()) . "." . $ext;
			$request->file('wil_logo')->move($destination_path, $md5_name);
			$img_file = "$md5_name";
			// set data
			$wilayah->wil_logo = $img_file;

		}else{
			$wilayah->wil_logo = $old_logo;
		}

		// save
		$wilayah->save();

		$warga_id = $request->warga_id;

		$warga = Warga::find($warga_id);

		$warga->warga_nama_depan = $request->warga_nama_depan;
		$warga->warga_nama_belakang = $request->warga_nama_belakang;
		$warga->warga_alamat = $request->warga_alamat;
		$warga->warga_hp = $request->warga_hp;
		$warga->warga_email = $request->warga_email;
		$warga->warga_no_rumah = $request->warga_no_rumah;

		$warga->save();

		$pengurus = Pengurus::firstWhere('warga_id', $warga_id);
		$pengurus->pengurus_jabatan = $request->pengurus_jabatan;
		$pengurus->save();

		$results = array(
			"wil_nama" => $wilayah->wil_nama,
			"wil_alamat" => $wilayah->wil_alamat,
			"wil_jenis" => $wilayah->wil_jenis,
			"kel_id" => $wilayah->kel_id
		);
		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;


		// return json response
		return response()->json($response);
	}
	/*== Update Status Langganan==*/
	public function update_status(Request $request)
	{
		$wil_id = $request->wil_id;
		$wil_nama = $request->wil_nama;
		$wil_status = $request->us_status_after;
		$wil_alasan_berhenti = $request->wil_alasan_berhenti;
		$wil_alasan_lain = $request->wil_alasan_lain;
		$us_tgl = Carbon::parse(now())->format('Y-m-d');
		$us_user_id = $request->us_user_id;
		$us_status_before = $request->us_status_before;
		$us_status_after = $request->us_status_after;
		$us_status_expire = $request->us_status_expire;

		$wilayah = Wilayah::find($wil_id);

		$wilayah->wil_status = $wil_status;
		$wilayah->wil_alasan_berhenti = $wil_alasan_berhenti;
		$wilayah->wil_expire = $us_status_expire;
		if($wil_status == 3 || $wil_status == 6){

			$wilayah->wil_berhenti = date("Y-m-d H:i:s");
		}
		if($wil_alasan_berhenti == 5){
			$wilayah->wil_alasan_lain = $wil_alasan_lain;
		}else{
			$wilayah->wil_alasan_lain = '-';
		}
		// save
		$wilayah->save();

		$us = new UbahStatus;

		$us->wil_id = $wil_id;
		$us->wil_nama = $wil_nama;
		$us->us_tgl = $us_tgl;
		$us->us_user_id = $us_user_id;
		$us->us_status_before = $us_status_before;
		$us->us_status_after = $us_status_after;
		$us->us_status_expire = $us_status_expire;

		$us->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses mengubah status berlangganan";


		// return json response
		return response()->json($response);
	}
	/*== H-5 Trial ==*/
	public function h_5_trial(Request $request, Wilayah $wilayah, Pengurus $pengurus)
	{
		//
		$img_url_billing_expired = ''.env('APP_URL').'/public/img/notification/billing_expired.png';
		echo $img_url_billing_expired;
		//get trial date wilayah
		$wilayah = $wilayah->get_notif_trial_h5();
		// print_r($wilayah);
		//varible global
        $_trial = GlobalVariable::find(1);
        //
        $_trial_expired = $_trial->global_value;
		//
		if(!empty($wilayah)){
			foreach ($wilayah as $row) {
				$wil_id = $row->wil_id;
				// echo "Wil ID :".$wil_id;

				$trial_date = date('Y-m-d',strtotime($row->wil_mulai_trial));
				//
				$trial_expired_date = date('Y-m-d',strtotime($trial_date . "+".$_trial_expired." days"));
				// echo "trial_expired_date :".$trial_expired_date;
				// echo "###";
				// $today = date('Y-m-d'); // haru ke 40 masa trial
				$today = date('2021-07-12'); // haru ke 40 masa trial

				$expired = new DateTime($trial_expired_date);
				$today = new DateTime($today);
				$interval = $expired->diff($today);
				// echo $interval->format('%R%a');
				$h_5 = $interval->format('%R%a');

				echo "Day : " .$h_5;

				// get pengurus ID
				// $pengurus = $pengurus->get_list_pengurus($wil_id);

				// print_r($pengurus);



				if($h_5 == '-5'){
					// echo "hari ini sudah memasuki h -5 masa trial, kirim notifikasi";
					// echo $wil_id;
					$pengurus = DB::table('pengurus as a')
		            ->join('warga as b','b.warga_id','=','a.warga_id')
		            ->join('masa_kepengurusan as c','c.mk_id','=','a.mk_id')
		            ->join('core_user as d','d.user_ref_id','=','a.warga_id')
	            	->select('d.fcm_token', 'b.warga_nama_depan', 'b.warga_id')
	            	->where('c.wil_id',$wil_id)
	            	->get();

	            	// print_r($pengurus);
	            	//

	    //         	//send to user peegurus
					// $endpoint = "https://fcm.googleapis.com/fcm/send";
					// $client = new \GuzzleHttp\Client();
					// //

					// foreach ($pengurus as $rows) {
					// 	$fcm_token = $rows->fcm_token;
					// 	$warga_nama = $rows->warga_nama_depan;
					// 	$warga_id =  $rows->warga_id;

					// 	//
					// 	$title = '5 Hari lagi masa trial!.';
					// 	$ket = '5 Hari lagi masa trial akun Anda akan segera Expired, upgrade sekarang.';
					// 	$body = substr(strip_tags($ket),0,100)."...";

					// 	//create json data
					// 	$data_json = [
					// 	        'notification' => [
					// 	        	'title' => 'Halo '.$warga_nama.'',
					// 	        	'body' => $ket,
					// 	        	'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
					// 	        	'sound'	=> 'alarm.mp3'
					// 	        ],
					// 	        'data' => [
					// 	        	'id' => ''.$wil_id.'',
					// 	        	'trial_date' => ''.$trial_date.'',
					// 	        	'page' => 'trial'
					// 	        ],
					// 	        'to' => ''.$fcm_token.''
					// 	    ];

					// 	$requestAPI = $client->post( $endpoint, [
					//         'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
					//         'body' => json_encode($data_json)
					//     ]);

					//     Notifikasi::create([
		   //                  'warga_id' => $warga_id,
		   //                  'notif_title' => substr($title,0,100),
		   //                  'notif_body' => $body,
		   //                  'notif_page' => 'trial',
		   //                  'page_id' => $wil_id,
		   //                  'page_sts' => 'trial_h_5',
		   //                  'notif_date' => Carbon::now()
		   //              ]);
					// }
				}
			}
		}


	}

    public function get_status(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $wilayah = Wilayah::find($wil_id);
        $paketLangganan = new PaketLangganan;
        $statusWilayah = ['Masa Trial','Masa Retensi Trial','Berhenti Trial','Berlangganan','Masa Retensi Berlangganan','Berhenti Berlangganan'];
        // response

        $retensi = GlobalVariable::where('global_name','retensi')->first();

        $response['status'] = "success";
		$response['message'] = "OK";
		$response['wil_status'] = $wilayah->wil_status;
        $response['status_wilayah'] = $statusWilayah[intval($wilayah->wil_status)-1];

        //trial
        $response['wil_mulai_trial'] = $wilayah->wil_mulai_trial;
        $response['wil_expire_trial'] = $paketLangganan->getExpireTrialDate($wil_id);
        $response['wil_end_retensi'] = Carbon::parse($wilayah->wil_retensi_trial)->addDays($retensi->global_value)->isoFormat('D MMMM Y');
        $response['wil_trial_remains'] = $paketLangganan->getExpireTrialRemainDays($wil_id);


        //berlangganan
        $response['wil_mulai_langganan'] = $wilayah->wil_mulai_langganan;
        $response['wil_expire'] = $wilayah->wil_expire;
        $response['wil_tag_due'] = $wilayah->wil_tag_due;

        //Rekening tujuan
        $response['wil_rek_no'] = empty($wilayah->wil_rek_no) ? '-' : $wilayah->wil_rek_no;
        $response['wil_rek_bank_tujuan'] = empty($wilayah->wil_rek_bank_tujuan) ? '-' : $wilayah->wil_rek_bank_tujuan;
        $response['wil_rek_atas_nama'] = empty($wilayah->wil_rek_atas_nama) ? '-' : $wilayah->wil_rek_atas_nama;


        if(!empty($wilayah->wil_expire))
            $response['expire_in_days'] = Carbon::now()->diffInDays(Carbon::parse($wilayah->wil_expire), false);

		// return json response
		return response()->json($response);
    }

	public function list(Request $request, Wilayah $wilayah)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $length = $request->get("length");
        $search = $request->get('search')['value'];

        $order =  $request->get('order');

         $col = 0;
         $dir = "desc";

         if(!empty($order)) {
             foreach($order as $o) {
                 $col = $o['column'];
                 $dir= $o['dir'];
             }
        }

         $columns_valid = array("wil_id", "wil_nama", "wil_alamat", "wil_mulai_trial", "wil_retensi_trial", "wil_mulai_langganan", "wil_expire");
         if(!isset($columns_valid[$col])) {
            $order = 'null';
        } else {
            $order = $columns_valid[$col];
        }

		$res = DB::table('wilayah as a')
		//->join('warga as b','a.wil_id','=','b.wil_id')
		->select(DB::raw('a.wil_id, a.wil_nama, a.wil_alamat,a.wil_mulai_trial,a.wil_retensi_trial,a.wil_mulai_langganan, a.wil_expire,a.wil_status'));

		if($request->get('wil_nama')){
			$res = $res->where('a.wil_nama', 'ILIKE', '%'.$request->wil_nama.'%');
		}
		if($request->get('wil_status')){
			$res = $res->where('a.wil_status', $request->wil_status);
		}
		if($request->get('wil_expire')){
			if($request->get('wil_expire')==1){
				$res = $res->whereRaw(DB::raw("a.wil_expire <= current_date - interval '10 days'"));
			}else{
				$res = $res->whereRaw(DB::raw("a.wil_expire > current_date - interval '10 days'"));
			}
		}

		//$res = $res->groupBy('a.wil_id', 'a.wil_nama', 'a.wil_alamat','a.wil_mulai_trial','a.wil_retensi_trial','a.wil_mulai_langganan','a.wil_expire','a.wil_status');

		if($search!=''){
			$res = $res->where('a.wil_nama','ilike',"%$search%");
		}
		if(isset($order)){
				$res = $res->orderBy($order, $dir);
		}else{
				$order = $res->orderBy('a.wil_id', 'desc');
		}
		if(isset($length) || isset($start)){
				$res = $res->skip($start)->take($length);
		}
		$res = $res->get();
        /*$res = DB::table('wilayah as a')
					->join('warga as b','a.wil_id','=','b.wil_id')
					->select(DB::raw('COUNT(*) jml_warga, a.wil_id, a.wil_nama, a.wil_alamat,a.wil_mulai_trial,a.wil_retensi_trial,a.wil_mulai_langganan, a.wil_expire,a.wil_status'))
					->groupBy('a.wil_id', 'a.wil_nama', 'a.wil_alamat','a.wil_mulai_trial','a.wil_retensi_trial','a.wil_mulai_langganan','a.wil_expire','a.wil_status');
        if($search!=''){
            $res = $res->where('a.wil_nama','ilike',"%$search%");
        }
		if(isset($order)){
			$res = $res->orderBy($order);
		}else{
			$order = $res->orderBy('wil_id');
		}
		if(isset($length) || isset($start)){
			$res = $res->skip($start)->take($length);
		}
        $res = $res->get();*/
        $i = 1;
        $data = array();
		if(!empty($res) || $res !=''){

			foreach($res as $r) {
				$status = array( 'Masa Trial', 'Masa Retensi Trial', 'Berhenti (dari Trial)', 'Berlangganan', 'Masa Retensi Berlangganan', 'Berhenti Berlangganan');

				$data[] = array(
					$start + $i,
					$r->wil_nama,
					$r->wil_alamat,
					(Carbon::parse($r->wil_mulai_trial)->format('d-m-Y') ),
					(Carbon::parse($r->wil_retensi_trial)->format('d-m-Y') ),
					$r->wil_mulai_langganan ? Carbon::parse($r->wil_mulai_langganan)->format('d-m-Y'):null,
					$r->wil_expire ? Carbon::parse($r->wil_expire)->format('d-m-Y'):null,
					$status[$r->wil_status-1],
					'<form  class="text-center" action="wilayah/'.$r->wil_id.'/destroy" method="POST"><a href="wilayah/'.$r->wil_id.'/show"><i class="fa fa-info-circle fa-lg" title="Detail"></i></a> <a href="wilayah/'.$r->wil_id.'/edit"><i class="fa fa-edit fa-lg text-success" title="Edit"></i> </a> <a href="#" id="hapus" data-id="'.$r->wil_id.'" data-nama="'.$r->wil_nama.'"> <i class="fa fa-trash fa-lg text-danger" title="Hapus"></i></a></form>'
				);
				$i++;
			}
			//total data lead
		   $total_sal = DB::table('wilayah as a')->get()->count();
		   //total filtered
		   $total_fil = DB::table('wilayah as a');
		   	if($request->get('wil_nama')){
				$total_fil = $total_fil->where('a.wil_nama', 'ILIKE', '%'.$request->wil_nama.'%');
			}
			if($request->get('wil_status')){
				$total_fil = $total_fil->where('a.wil_status', $request->wil_status);
			}
			if($request->get('wil_expire')){
				if($request->get('wil_expire')==1){
					$total_fil = $total_fil->whereRaw(DB::raw("a.wil_expire <= current_date - interval '10 days'"));
				}else{
					$total_fil = $total_fil->whereRaw(DB::raw("a.wil_expire > current_date - interval '10 days'"));
				}
			}
		   //$total_fil = $total_fil->groupBy('b.wil_id');
		   if($search!=''){
				$total_fil = $total_fil->where('a.wil_nama','ilike',"%$search%");
			}
			$total_fil = $total_fil->get()->count();

			$output = array(
				"draw" => $draw,
				"recordsTotal" => $total_sal,
				"recordsFiltered" => $total_fil,
				"data" => $data
			);
		}else{
			$output = array(
				"draw" => $draw,
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => $data
			);
		}

        /*if(empty($info) || $info ='')
        {
            $response['status'] = "error";
            $response['message'] = "Wilayah not found";
            return response()->json($response);
            exit();
        }*/


        /*$result = Wilayah::where('wilayah.wil_head', '1')->latest()->get();
         $response['status'] = "success";
         $response['message'] = "OK";
         $response['results'] = $result;*/

         // return json response
         return response()->json($output, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

    }
	public function dash_wilayah(){
		$total_wil = DB::table('wilayah as a')
		   ->join('warga as b','a.wil_id','=','b.wil_id')
		   ->select(DB::raw('COUNT(*) jml_warga'))
		   ->groupBy('b.wil_id')->get()->count();

		$total_wil_month = DB::table('wilayah as a')
			->whereRaw('EXTRACT(month FROM a.wil_mulai_trial) = EXTRACT(month FROM NOW())')
			->get()->count();

		$total_wil_pmonth = DB::table('wilayah as a')
			->whereRaw('EXTRACT(month FROM a.wil_mulai_trial) < EXTRACT(month FROM NOW())')
			->get()->count();

		$total_wil_day = DB::table('wilayah as a')
			->whereRaw('EXTRACT(day FROM a.wil_mulai_trial) = EXTRACT(day FROM NOW())')
			->whereRaw('EXTRACT(month FROM a.wil_mulai_trial) = EXTRACT(month FROM NOW())')
			->get()->count();

		$total_wil_pday = DB::table('wilayah as a')
			->whereRaw('a.wil_mulai_trial < NOW()')
			->get()->count();

		$total_wil_trial = DB::table('wilayah as a')
			->where('a.wil_status','=','1')
			->get()->count();

		$total_wil_trial_10d = DB::table('wilayah as a')
			->whereRaw("a.wil_status = '1' AND a.wil_retensi_trial <= current_date - interval '10 days'")
			->get()->count();

		$total_wil_berhenti = DB::table('wilayah as a')
			->where('a.wil_status','=','6')
			->get()->count();

		$total_wil_berhenti_month = DB::table('wilayah as a')
			->whereRaw("date_part('month', a.wil_berhenti)=DATE_PART('month', NOW()) AND a.wil_status ilike '6'")
			->get()->count();


		//$alasan = array("Sudah tidak membutuhkan Aplikasi","Terbebani dengan Biaya","Aplikasi tidak sesuai dengan yang diharapkan", "Pelayanan kurang baik", "Alasan lain yaitu");

		$alasan1 = DB::table('wilayah as a')->where('a.wil_status','=','6')->where('a.wil_alasan_berhenti','=','1')->get()->count();
		$alasan2 = DB::table('wilayah as a')->where('a.wil_status','=','6')->where('a.wil_alasan_berhenti','=','2')->get()->count();
		$alasan3 = DB::table('wilayah as a')->where('a.wil_status','=','6')->where('a.wil_alasan_berhenti','=','3')->get()->count();
		$alasan4 = DB::table('wilayah as a')->where('a.wil_status','=','6')->where('a.wil_alasan_berhenti','=','4')->get()->count();
		$alasan5 = DB::table('wilayah as a')->where('a.wil_status','=','6')->where('a.wil_alasan_berhenti','=','5')->get()->count();


		$res = array(
			"total_wilayah" => $total_wil,
			"wilayah_month" => $total_wil_month,
			"wilayah_pmonth" => $total_wil_pmonth,
			"wilayah_day" => $total_wil_day,
			"wilayah_pday" => $total_wil_pday,
			"total_trial" => $total_wil_trial,
			"total_trial_10d" => $total_wil_trial_10d,
			"total_berhenti" => $total_wil_berhenti,
			"total_berhenti_month" => $total_wil_berhenti_month,
			"total_alasan1" => $alasan1,
			"total_alasan2" => $alasan2,
			"total_alasan3" => $alasan3,
			"total_alasan4" => $alasan4,
			"total_alasan5" => $alasan5,
		);
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['res'] = $res;

		// return json response
		return response()->json($response);

	}

	public function stat_status()
	{
		/*SELECT COUNT(w.wil_id) AS jml, kab.kabkota_nama
		FROM wilayah AS w
		JOIN kelurahan AS kel ON w.kel_id=kel.kel_id
		JOIN kecamatan AS kec ON kel.kec_id=kec.kec_id
		JOIN kabkota AS kab ON kec.kabkota_id=kab.kabkota_id
		GROUP BY kab.kabkota_id, kab.kabkota_nama*/
		$stat_wil = DB::table('wilayah as w')
		->select(DB::raw('COUNT(w.wil_id) jml'), 'w.wil_status', DB::raw('(SELECT COUNT(1) FROM wilayah) total'))
		->groupBy('w.wil_status')->get();

		$response = $stat_wil;

		// return json response
		return response()->json($response);
	}

	public function stat_wilayah(){
		/*SELECT COUNT(w.wil_id) AS jml, kab.kabkota_nama
		FROM wilayah AS w
		JOIN kelurahan AS kel ON w.kel_id=kel.kel_id
		JOIN kecamatan AS kec ON kel.kec_id=kec.kec_id
		JOIN kabkota AS kab ON kec.kabkota_id=kab.kabkota_id
		GROUP BY kab.kabkota_id, kab.kabkota_nama*/
		$stat_wil = DB::table('wilayah as w')
		   ->join('kelurahan as kel','w.kel_id','=','kel.kel_id')
		   ->join('kecamatan as kec','kel.kec_id','=','kec.kec_id')
		   ->join('kabkota as kab','kec.kabkota_id','=','kab.kabkota_id')
		   ->select(DB::raw('COUNT(w.wil_id) jml'), 'kab.kabkota_nama', DB::raw('(SELECT COUNT(1) FROM wilayah) AS total_wil'))
		   ->groupBy('kab.kabkota_id','kab.kabkota_nama')->get();

		$response = $stat_wil;

		// return json response
		return response()->json($response);
	}
	public function stat_pwilayah(){
		/*SELECT COUNT(1),
		CONCAT(TO_CHAR(TO_DATE(EXTRACT(month FROM w.wil_mulai_trial)::TEXT, 'MM'), 'Mon'),' ',
		TO_CHAR(TO_DATE(EXTRACT(year FROM w.wil_mulai_trial)::TEXT, 'YY'), 'YY')) my
		FROM wilayah AS w
		GROUP BY EXTRACT(month FROM w.wil_mulai_trial), EXTRACT(year FROM w.wil_mulai_trial)*/
		$stat_wil = DB::table('wilayah as w')
		   ->select(DB::raw('COUNT(1) penambahan'), DB::raw("CONCAT(TO_CHAR(TO_DATE(EXTRACT(month FROM w.wil_mulai_trial)::TEXT, 'MM'), 'Mon'),' ',
		   TO_CHAR(TO_DATE(EXTRACT(year FROM w.wil_mulai_trial)::TEXT, 'YY'), 'YY')) mon"))
		   ->groupBy(DB::raw('EXTRACT(month FROM w.wil_mulai_trial)'),DB::raw('EXTRACT(year FROM w.wil_mulai_trial)'))
			->orderBy(DB::raw("EXTRACT(month FROM w.wil_mulai_trial)"))
		   ->get();

		$response = $stat_wil;

		// return json response
		return response()->json($response);
	}
	/*== Delete ==*/
	public function delete(Request $request)
	{
		$wil_id = $request->wil_id;

		// get data
		$info = Wilayah::find($wil_id);
		// theme checking
		if(empty($info))
		{
			$response['status'] = "error";
			$response['message'] = "Informasi with ID : $wil_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
			// delete
			Wilayah::find($wil_id)->delete();
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
	/*== Delete ==*/
	public function delete_crm(Request $request)
	{
		$wil_id = $request->wil_id;

		// get data
		$info = Wilayah::find($wil_id);
		// theme checking
		if(empty($info))
		{
			$response['status'] = "error";
			$response['message'] = "Informasi with ID : $wil_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
			// delete
			//Wilayah::find($wil_id)->delete();
			Keuangan::where('wil_id', $wil_id)->delete();
			WargaUndang::where('wil_id', $wil_id)->delete();
			Tarif::where('wil_id', $wil_id)->delete();
			Phonebook::where('wil_id', $wil_id)->delete();
			Info::where('wil_id', $wil_id)->delete();
			Peraturan::where('wil_id', $wil_id)->delete();
			Billing::where('wil_id', $wil_id)->delete();

			//tagihan related
			$warga_id = Warga::where('wil_id', $wil_id)->select('warga_id')->get();
			foreach($warga_id as $w){
				$bbd = DB::table('warga AS w')
						->join('tagihan AS t','w.warga_id','=', 't.warga_id')
						->join('periode_tagihan AS pt','t.pt_id','=','pt.pt_id')
						->leftJoin('bukti_bayar AS bb','w.warga_id','=','bb.warga_id')
						->leftJoin('bb_detil AS bd','bb.bb_id','=','bd.bb_id')
						->where('w.wil_id', '=', $wil_id)
						->select('bd.bb_detil_id', 't.tag_id', 'bb.bb_id', 'pt.pt_id')
						->get();
				foreach($bbd as $d){
					DB::table('bb_detil')->where('tag_id', '=', $d->tag_id)->delete();
					DB::table('bukti_bayar')->where('bb_id', '=', $d->bb_id)->delete();
					DB::table('tagihan')->where('pt_id', '=', $d->pt_id)->delete();
					DB::table('periode_tagihan')->where('pt_id', '=', $d->pt_id)->delete();
				}
				//DB::table('tagihan')->where('warga_id', '=', $val->warga_id)->delete();

			}

			/*C). Berhubungan dengan jual/beli (patokan wil_id di table usaha). urutan delete :
			1. jadwal_buka
			2. produk
			3. usaha*/
			//jual-beli
			$jb = DB::table('usaha AS u')
					->where('u.wil_id','=', $wil_id)
					->select('u.usaha_id')
					->get();
			foreach($jb as $d){
				DB::table('jadwal_buka')->where('usaha_id', '=', $d->usaha_id)->delete();
				DB::table('produk')->where('usaha_id', '=', $d->usaha_id)->delete();
				DB::table('usaha')->where('usaha_id', '=', $d->usaha_id)->delete();
			}

			/*D). Berhubungan dengan pengaduan  (patokan wil_id di table komplain). urutan delete :
			1. komen_komplain
			2. komplain*/
			$komp = DB::table('komplain AS k')
					->join('warga AS w','k.warga_id','=','w.warga_id')
					->where('w.wil_id','=', $wil_id)
					->select('k.komp_id')
					->get();
			foreach($komp as $d){
				DB::table('komen_komplain')->where('komp_id', '=', $d->komp_id)->delete();
				DB::table('komplain')->where('komp_id', '=', $d->komp_id)->delete();
			}


			/*E). Berhubungan dengan darurat (patokan wil_id di table kategori_panic). urutan delete :
			1. penerima_panic
			2. panic
			3. kategori_panic*/
			$pan = DB::table('kategori_panic AS kp')
					->where('kp.wil_id','=', $wil_id)
					->select('kp.kp_id')
					->get();
			foreach($pan as $d){
				DB::table('penerima_panic')->where('kp_id', '=', $d->kp_id)->delete();
				DB::table('panic')->where('kp_id', '=', $d->kp_id)->delete();
				DB::table('kategori_panic')->where('kp_id', '=', $d->kp_id)->delete();
			}

			/*F). Berhubungan dengan kepengurusan (patokan wil_id di table masa_kepengurusan). urutan delete :
			1. pengurus
			2. masa_kepengurusan*/
			$pen = DB::table('masa_kepengurusan AS mk')
					->where('mk.wil_id','=', $wil_id)
					->select('mk.mk_id')
					->get();
			foreach($pen as $d){
				DB::table('pengurus')->where('mk_id', '=', $d->mk_id)->delete();
				DB::table('masa_kepengurusan')->where('mk_id', '=', $d->mk_id)->delete();
			}

			/*G). Berhubungan dengan notifikasi (patokan warga_id). urutan delete :
			1. notifikasi*/
			$not = DB::table('notifikasi AS n')
					->join('warga AS w','n.warga_id','=','w.warga_id')
					->where('w.wil_id','=', $wil_id)
					->select('n.warga_id')
					->get();
			foreach($not as $d){
				DB::table('notifikasi')->where('warga_id', '=', $d->warga_id)->delete();
			}


			/*H). Berhubungan dengan chat (patokan wil_id di table percakapan). urutan delete :
			1. pesan
			2. percakapan*/
			$komp = DB::table('percakapan AS a')
					->leftJoin('pesan AS b','a.percakapan_id','=','b.percakapan_id')
					->where('a.wil_id','=', $wil_id)
					->select('a.percakapan_id')
					->get();
			foreach($komp as $d){
				DB::table('pesan')->where('percakapan_id', '=', $d->percakapan_id)->delete();
				DB::table('percakapan')->where('wil_id', '=', $wil_id)->delete();
			}


			/*I). Setelah memastikan semua table-table di atas terhapus, berikut urutan untuk menghapus WARGA dan WILAYAH :
			1. anggota_keluarga
			2. core_user (patokan : user_ref_id --> ini adalah warga_id)
			3. warga
			4. kategori_bangunan
			5. wilayah*/

			foreach($warga_id as $d){
				DB::table('anggota_keluarga')->where('warga_id', '=', $d->warga_id)->delete();
				DB::table('core_user')->where('user_ref_id', '=', $d->warga_id)->delete();

			}
			DB::table('warga')->where('wil_id', '=', $wil_id)->delete();
			DB::table('kategori_bangunan')->where('wil_id', '=', $wil_id)->delete();
			DB::table('wilayah')->where('wil_id', '=', $wil_id)->delete();



		}
		catch(\Exception $e)

		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the Informasi";
			$response['result'] = $e->getMessage();
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses menghapus wilayah";

		// return json response
		return response()->json($response);
	}
	/*==reset billing==*/
	public function reset_billing(Request $request)
	{
		$wil_id = $request->wil_id;

		// get data
		$info = Wilayah::find($wil_id);
		// theme checking
		if(empty($info))
		{
			$response['status'] = "error";
			$response['message'] = "Informasi with ID : $wil_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
			/*
			Step-step nya mas Alif untuk ngereset (contoh kasus Apartemen Indah wil_id = 26) :
			1. Delete semua data di table billing untuk wilayah Apartemen Indah
			2. Set data di table wilayah utk Apartemen Indah sebagai berikut:
			wil_mulai_langganan = null;
			wil_expire = null;
			wil_status = 1;
			pl_id = null;
			wil_req_berhenti = null;*/

			Billing::where('wil_id', $wil_id)->delete();

			$wil = Wilayah::find($wil_id);

			$wil->wil_mulai_langganan = NULL;
			$wil->wil_expire = NULL;
			$wil->wil_status = 1;
			$wil->pl_id = NULL;
			$wil->wil_req_berhenti = NULL;

			$wil->save();




		}
		catch(\Exception $e)

		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the Informasi";
			$response['result'] = $e->getMessage();
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses melakukan reset billing wilayah";

		// return json response
		return response()->json($response);
	}


	/*== Add Pengurus==*/
	public function add_pengurus(Request $request)
	{
        try{

			$pengurus = new Pengurus;
			$pengurus->pengurus_jabatan = $request->pengurus_jabatan;
			$pengurus->warga_id = $request->warga_id;
			$pengurus->updated_at = Carbon::now();

            // response
            $response['status'] = "success";
            $response['message'] = "OK";
            $response['results'] = "Sukses menyimpan pengurus baru";
            // return json response
            return response()->json($response);
         }
         catch(\Exception $e){
             // response
             $response['status'] = "error";
             $response['message'] = "error";
             $response['results'] =  $e->getMessage();
             // return json response
             return response()->json($response);
         }


	}

	/*== Update ==*/
	public function update_pengurus(Request $request)
	{
		$pen_id = $request->pengurus_id_edit;

		$pen = Pengurus::find($pen_id);

        $pen->warga_id = $request->warga_id_edit_pen;
        $pen->pengurus_jabatan = $request->pengurus_jabatan_edit;

        $pen->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses mengubah data pengurus";

		// return json response
		return response()->json($response);
	}
	//update admin
	public function update_admin(Request $request)
	{
		$pen_id = $request->admin_pengurus_id;
		$wil_id = $request->wil_id;

		$pen = Pengurus::find($pen_id);

        $pen->warga_id = $request->admin_warga_id;

        $pen->save();

		// response
		/*$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses mengubah admin";*/

		// get data
		$info = DB::table('warga as b')
		->join('pengurus as c', 'c.warga_id', '=', 'b.warga_id')
		->where('b.wil_id', $wil_id)
			->select('b.*', 'c.pengurus_jabatan', 'c.pengurus_id')
			->first();
		//return $info;
		return response()->json($info);

	}


	//dapat nama depan & belakang
	function getFirstName($name) {
		return implode(' ', array_slice(explode(' ', $name), 0, -1));
	}

	function getLastName($name) {
		return array_slice(explode(' ', $name), -1)[0];
	}

	/*== Generate Kode Wilayah ==*/
	public function generate_code(Request $request)
	{
		// random
		$wilayah_ = DB::table('wilayah as a')
					->where('a.wil_id', '!=', 1)
					->select('a.*')
					->get();

		foreach ($wilayah_ as $row) {

			$random_string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$wil_kode = substr(str_shuffle(str_repeat($random_string, 15)), 0, 6);
			//
			$wil_id = $row->wil_id;
			$code = $wil_id.''.$wil_kode;

			//
			$wilayah = Wilayah::find($wil_id);
			$wilayah->wil_kode = $code;
			$wilayah->save();
		}

		return response()->json($wilayah);


	}
}
