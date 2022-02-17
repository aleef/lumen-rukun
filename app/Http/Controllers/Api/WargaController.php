<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Str;
use File;
use Bitly;
use Response;
//use Mail;
use Illuminate\Support\Facades\Mail;
use App\Warga;
use App\WargaTemp;
use App\Wilayah;
use App\User;
use App\Pengurus;
use App\Mk;
use App\Iplm;
use App\Wu;
use App\Kb;
use App\GlobalVariable;
use App\SendPhoneMessage;

class WargaController extends Controller
{
	private $ctrl = 'warga';
	private $title = 'Warga';

	/*==  List Data ==*/
	public function list(Request $request, Warga $warga)
	{
		$wil_id = $request->wil_id;
		$warga_id = $request->warga_id;
		$keyword = $request->keyword;
		// validate param
		if($wil_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "wil_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$warga = $warga->get_list($wil_id, $keyword, $warga_id);
		if(empty($warga))
		{
			$response['status'] = "error";
			$response['message'] = "Warga not found";
			return response()->json($response);
			exit();
		}

		$i=0;
        foreach($warga as $row)
        {
            if($row->warga_foto!='')
            {
                $warga_foto = URL('public/img/pp/'.$row->warga_foto);
            }else{
                $warga_foto = URL('public/img/pp/default.png');
            }

            $result[$i]['warga_id'] = $row->warga_id;
            $result[$i]['warga_nama_depan'] = $row->warga_nama_depan;
            $result[$i]['warga_nama_belakang'] = $row->warga_nama_belakang;
            $result[$i]['warga_email'] = $row->warga_email;
            $result[$i]['warga_alamat'] = $row->warga_alamat;
            $result[$i]['warga_no_rumah'] = $row->warga_no_rumah;
            $result[$i]['warga_geo'] = $row->warga_geo;
            $result[$i]['warga_foto'] = $warga_foto;
            $result[$i]['warga_hp'] = $row->warga_hp;
            $result[$i]['warga_tgl_lahir'] = Carbon::parse($row->warga_tgl_lahir)->format('d M Y');
            $result[$i]['wil_id'] = $row->wil_id;
            $result[$i]['warga_status'] = $row->warga_status;
            $i++;
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $result;

		// return json response
		return response()->json($response);

	}

    public function list_registered(Request $request, Warga $warga)
	{
		$wil_id = $request->wil_id;
		$warga_id = $request->warga_id;
		$keyword = $request->keyword;

        $page = empty($request->page) ? 1 : $request->page;
        $limit = empty($request->limit) ? 20 : $request->limit;


		// validate param
		if($wil_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "wil_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$warga = $warga->get_list_limited($wil_id, $keyword, $warga_id, $page, $limit);
		if(empty($warga))
		{
			$response['status'] = "error";
			$response['message'] = "Warga not found";
			return response()->json($response);
			exit();
		}

		$i=0;
        $result = [];
        foreach($warga as $row)
        {
            if($row->warga_foto!='')
            {
                $warga_foto = URL('public/img/pp/'.$row->warga_foto);
            }else{
                $warga_foto = null;
            }

            $result[$i]['warga_id'] = $row->warga_id;
            $result[$i]['warga_nama_depan'] = $row->warga_nama_depan;
            $result[$i]['warga_nama_belakang'] = $row->warga_nama_belakang;
            $result[$i]['warga_email'] = $row->warga_email;
            $result[$i]['warga_alamat'] = $row->warga_alamat;
            $result[$i]['warga_no_rumah'] = $row->warga_no_rumah;
            $result[$i]['warga_geo'] = $row->warga_geo;
            $result[$i]['warga_foto'] = $warga_foto;
            $result[$i]['warga_hp'] = $row->warga_hp;
            $result[$i]['warga_tgl_lahir'] = Carbon::parse($row->warga_tgl_lahir)->format('d M Y');
            $result[$i]['wil_id'] = $row->wil_id;
            $result[$i]['warga_status'] = $row->warga_status;
            $i++;
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $result;

		// return json response
		return response()->json($response);

	}

    public function list_unregistered(Request $request, Warga $warga)
	{
		$wil_id = $request->wil_id;
		$keyword = $request->keyword;

        $response =  array('status' => 'error', 'message' => '', 'results' => []);

        // validate param
		if($wil_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "wil_id is required fields";
			return response()->json($response);
		}

		// get data
		$warga = $warga->get_list_unregistered($wil_id, $keyword);
		if(empty($warga))
		{
			$response['status'] = "error";
			$response['message'] = "Warga not found";
			return response()->json($response);
		}

        $wil_foto = '';
        $wilayah = Wilayah::find($wil_id);
        if($wilayah->wil_foto!='')
			$wil_foto = URL('public/img/wilayah/'.$wilayah->wil_foto);
		else
			$wil_foto = URL('public/img/wilayah/default.jpg');
        $wilayah->wil_foto = $wil_foto;

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $warga;
        $response['wilayah'] = $wilayah;



		// return json response
		return response()->json($response);

	}

    public function detail_info(Request $request) {
        $response =  array('status' => 'error', 'message' => '', 'results' => []);

        $warga_id = $request->warga_id;
        if(empty($warga_id)) {
            $response['message'] = 'Warga ID tidak boleh kosong';
            return response()->json($response);
        }

        $warga = Warga::find($warga_id);
        $data = json_decode(json_encode($warga), true);

        if(!empty($warga->warga_tgl_lahir))
            $data['warga_tgl_lahir'] = Carbon::parse($warga->warga_tgl_lahir)->isoFormat('D MMMM Y');
        else
            $data['warga_tgl_lahir'] = '-';

        if($warga->warga_foto!='') {
            $data['warga_foto'] = URL('public/img/pp/'.$warga->warga_foto);
        }else{
            $data['warga_foto'] = null;
        }

        switch ($warga->warga_status_rumah) {

            case '':
				$data['status_rumah'] = '-';
				break;

            case '-':
                $data['status_rumah'] = '-';
                break;

			case '0':
				$data['status_rumah'] = '-';
				break;

			case '1':
				$data['status_rumah'] = 'Rumah Sendiri';
				break;

			case '2':
				$data['status_rumah'] = 'Rumah Keluarga';
				break;

			default:
				$data['status_rumah'] = 'Kontrak/Sewa';
				break;
		}

		switch ($warga->warga_status) {

            case '':
				$data['status_warga'] = '-';
				break;
            case '-':
                $data['status_warga'] = '-';
                break;

			case '0':
				$data['status_warga'] = '-';
				break;

			case '1':
				$data['status_warga'] = 'Penduduk Tetap';
				break;

			default:
				$data['status_warga'] = 'Penduduk Non Permanen';
				break;
		}

        $response['status'] = 'success';
        $response['message'] = 'OK';
        $response['results'] = $data;

        return response()->json($response);
    }

	/*==  Detail Data ==*/
	public function detailcrm($warga_id, Request $request, Warga $warga)
	{
		// validate param
		if($warga_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "warga_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$warga = $warga->get_detail($warga_id);
		// $warga = Info::find($warga_id);
		if(empty($warga))
		{
			$response['status'] = "error";
			$response['message'] = "Warga not found";
			return response()->json($response);
			exit();
		}

		//
		if($warga->warga_foto!='')
		{
			$warga_foto = URL('public/img/pp/'.$warga->warga_foto);
		} else {
			$warga_foto = URL('public/img/pp/default.png');
		}

		switch ($warga->warga_status_rumah) {
            case '':
				$warga_status_rumah = '-';
				break;

            case '-':
                $warga_status_rumah = '-';
                break;

			case '0':
				$warga_status_rumah = '-';
				break;

			case '1':
				$warga_status_rumah = '1';
				break;

			case '2':
				$warga_status_rumah = '2';
				break;

			default:
				$warga_status_rumah = '3';
				break;
		}

		switch ($warga->warga_status) {
            case '':
				$warga_status = '-';
				break;

            case '-':
                $warga_status = '-';
                break;

			case '0':
				$warga_status = '-';
				break;

			case '1':
				$warga_status = '1';
				break;

			default:
				$warga_status = '2';
				break;
		}

		$results = array(
			"warga_id" => $warga->warga_id,
			"warga_nama_depan" => $warga->warga_nama_depan,
			"warga_nama_belakang" => $warga->warga_nama_belakang,
			"warga_email" => $warga->warga_email,
			"warga_alamat" => $warga->warga_alamat,
			"warga_no_rumah" => $warga->warga_no_rumah,
			"warga_geo" => $warga->warga_geo,
			"warga_foto" => $warga_foto,
			"warga_hp" => $warga->warga_hp,
			"warga_tgl_lahir" => $warga->warga_tgl_lahir,
			"wil_id" => $warga->wil_id,
			"warga_status" => $warga_status,
			"warga_status_rumah" => $warga_status_rumah,
			"kb_id" => $warga->kb_id,
			"wil_nama" => $warga->wil_nama,
			"kel_id" => $warga->kel_id,
			"wil_alamat" => $warga->wil_alamat,
			"wil_geolocation" => $warga->wil_geolocation,
			"wil_foto" => $warga->wil_foto,
			"wil_jenis" => $warga->wil_jenis,
			"fcm_token" => $warga->fcm_token,
			"kb_keterangan" => $warga->kb_keterangan,
			"kb_tarif_ipl" => $warga->kb_tarif_ipl,
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);

	}
	public function detail($warga_id, Request $request, Warga $warga)
	{
		// validate param
		if($warga_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "warga_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$warga = $warga->get_detail($warga_id);
		// $warga = Info::find($warga_id);
		if(empty($warga))
		{
			$response['status'] = "error";
			$response['message'] = "Warga not found";
			return response()->json($response);
			exit();
		}

		//
		if($warga->warga_foto!='')
		{
			$warga_foto = URL('public/img/pp/'.$warga->warga_foto);
		} else {
			$warga_foto = URL('public/img/pp/default.png');
		}

		switch ($warga->warga_status_rumah) {
			case '':
				$warga_status_rumah = '-';
				break;

            case '-':
                $warga_status_rumah = '-';
                break;

            case '0':
                $warga_status_rumah = '-';
                break;

			case '1':
				$warga_status_rumah = 'Rumah Sendiri';
				break;

			case '2':
				$warga_status_rumah = 'Rumah Keluarga';
				break;

			default:
				$warga_status_rumah = 'Kontrak/Sewa';
				break;
		}

		switch ($warga->warga_status) {
			case '':
				$warga_status = '-';
				break;

            case '-':
                $warga_status = '-';
                break;

            case '0':
                $warga_status = '-';
                break;

			case '1':
				$warga_status = 'Penduduk Tetap';
				break;

			default:
				$warga_status = 'Penduduk Non Permanen';
				break;
		}

		$results = array(
			"warga_id" => $warga->warga_id,
			"warga_nama_depan" => $warga->warga_nama_depan,
			"warga_nama_belakang" => $warga->warga_nama_belakang,
			"warga_email" => $warga->warga_email,
			"warga_alamat" => $warga->warga_alamat,
			"warga_no_rumah" => $warga->warga_no_rumah,
			"warga_geo" => $warga->warga_geo,
			"warga_foto" => $warga_foto,
			"warga_hp" => $warga->warga_hp,
			"warga_tgl_lahir" => (!empty($warga->warga_tgl_lahir)) ? Carbon::parse($warga->warga_tgl_lahir)->isoFormat('D MMMM Y') : "",
            "tgl_lahir" => $warga->warga_tgl_lahir,
			"wil_id" => $warga->wil_id,
			"warga_status" => $warga_status,
			"warga_status_rumah" => $warga_status_rumah,
            "warga_status_id" => empty($warga->warga_status) ? "0" : $warga->warga_status,
            "warga_status_rumah_id" => empty($warga->warga_status_rumah) ? "0" : $warga->warga_status_rumah,
			"kb_id" => $warga->kb_id,
			"wil_nama" => $warga->wil_nama,
			"kel_id" => $warga->kel_id,
			"wil_alamat" => $warga->wil_alamat,
			"wil_geolocation" => $warga->wil_geolocation,
			"wil_foto" => $warga->wil_foto,
			"wil_jenis" => $warga->wil_jenis,
			"fcm_token" => $warga->fcm_token,
			"kb_keterangan" => $warga->kb_keterangan,
			"kb_tarif_ipl" => $warga->kb_tarif_ipl,
            "wil_rek_no" => empty($warga->wil_rek_no) ? '-' : $warga->wil_rek_no,
            "wil_rek_bank_tujuan" => empty($warga->wil_rek_bank_tujuan) ? '-' : $warga->wil_rek_bank_tujuan,
            "wil_rek_atas_nama" => empty($warga->wil_rek_atas_nama) ? '-' : $warga->wil_rek_atas_nama,
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);

	}



	/*== Sign Up ==*/
	public function signup(Request $request)
	{


		try
    	{
			// login
			$password = $request->password;
			$email = $request->email;

			// account warga
			$warga_nama_depan = $request->warga_nama_depan;
			$warga_nama_belakang = $request->warga_nama_belakang;
			$warga_hp = $request->warga_hp;
			$warga_email = $request->email;
			$warga_alamat = $request->warga_alamat;
			$warga_no_rumah = $request->warga_no_rumah;
			$warga_geo = $request->warga_geo;
			$warga_status = $request->warga_status;
			$warga_status_rumah = $request->warga_status_rumah;

			// mk
			$mk_periode_mulai = $request->mk_periode_mulai;
			$mk_periode_akhir = $request->mk_periode_akhir;

			// kb
			$kb_keterangan = $request->kb_keterangan;
			$kb_tarif_ipl = $request->kb_tarif_ipl;

			//jabatan pengurus
			$pengurus_jabatan = $request->pengurus_jabatan;

			//fcm
			$fcm_token = $request->fcm_token;


			//account wilayah
			$kel_id = $request->kel_id;
			$wil_jenis = $request->wil_jenis;
			$wil_nama = $request->wil_nama;
			$wil_alamat = $request->wil_alamat;
			$wil_geolocation = $request->wil_geolocation;
			$wil_foto = $request->wil_foto;
			$wil_logo = $request->wil_logo;
			$wil_kode = '-';



			// validate param
			if($warga_nama_depan=='' || $warga_hp=='' || $warga_email=='')
			{
				$response['status'] = "error";
				$response['message'] = "Warga name, email, hp are required fields";
				return response()->json($response);
				exit();
			}

			//validate hp
			$warga = Warga::where('warga_hp', $warga_hp)->first();
			if(!empty($warga))
			{
				$response['status'] = "error";
				$response['message'] = "HP : $warga_hp sudah terdaftar";
				return response()->json($response);
				exit();
			}

			$wt = WargaTemp::where('wt_hp', $warga_hp)->first();
			if(!empty($wt))
			{
				$response['status'] = "error";
				$response['message'] = "HP : $warga_hp sudah terdaftar";
				return response()->json($response);
				exit();
			}

			// validate email
			$warga_email_ = Warga::where('warga_email', $email)->first();
			if(!empty($warga_email_))
			{
				$response['status'] = "error";
				$response['message'] = "Email : $email sudah terdaftar";
				return response()->json($response);
				exit();
			}

			$wt_email = WargaTemp::where('wt_email', $email)->first();
			if(!empty($wt_email))
			{
				$response['status'] = "error";
				$response['message'] = "Email : $email sudah terdaftar";
				return response()->json($response);
				exit();
			}

			$user = User::where('user_email',$email)->first();
			if(!empty($user))
			{
				$response['status'] = "error";
				$response['message'] = "Email : $email sudah terdaftar";
				return response()->json($response);
				exit();
			}

			$wil = new Wilayah;
			$warga = new Warga;
			$user = new User;
			$mk = new Mk;
			$pengurus = new Pengurus;
			$iplm = new Iplm;
			$kb = new Kb;

			//varible global
			$_trial = GlobalVariable::find(1);
			//$_retensi = GlobalVariable::find(2);

			//
			//$_end_retensi_date = ($_trial->global_value + $_retensi->global_value);
	        $num_trial_expired_days = ($_trial->global_value + 1);
			//
			// print_r($_end_retensi_date);
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
				$img->fit(500);
				$img->save(public_path("img/wilayah/$md5_name.$ext"));

				// set data
				$wil->wil_foto = $img_file;

			}
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
				$img->fit(500);
				$img->save(public_path("img/logo_wilayah/$md5_name.$ext"));

				// set data
				$wil->wil_logo = $img_file;

			}
			// set data wilayah
			$register_date = date('Y-m-d');
			$wil->kel_id = $kel_id;
			$wil->wil_jenis = $wil_jenis;
			$wil->wil_nama = $wil_nama;
			$wil->wil_alamat = $wil_alamat;
			$wil->wil_geolocation = $wil_geolocation;
			$wil->wil_status = 1;
			$wil->wil_mulai_trial = $register_date;
			$wil->wil_retensi_trial = date('Y-m-d',strtotime($register_date . "+".$num_trial_expired_days." days")); // 60 hari dari masa mulai trial
			$wil->wil_kode = $wil_kode;
			$wil->save();

			$last_wil_id = $wil->wil_id;

			// set data masa kepengurusan
			$mk->wil_id = $last_wil_id;
			$mk->mk_periode_mulai = $mk_periode_mulai;
			$mk->mk_periode_akhir = $mk_periode_akhir;
			$mk->mk_status = 1;
			// save
			$mk->save();


			$kb->wil_id = $last_wil_id;
			$kb->kb_keterangan = $kb_keterangan;
			$kb->kb_tarif_ipl = $kb_tarif_ipl;
			// save
			$kb->save();

			$last_kb_id = $kb->kb_id;

			// set data warga
			$warga->warga_nama_depan = $warga_nama_depan;
			$warga->warga_nama_belakang = $warga_nama_belakang;
			$warga->warga_hp = $warga_hp;
			$warga->warga_email = $warga_email;
			$warga->warga_alamat = $warga_alamat;
			$warga->warga_no_rumah = $warga_no_rumah;
			$warga->warga_geo = $warga_geo;
			$warga->wil_id = $last_wil_id;
			$warga->kb_id = $last_kb_id;
			$warga->warga_status = $wil->warga_status;
			$warga->warga_status_rumah = $wil->warga_status_rumah;

			// save
			$warga->save();


			//set data pengurus
			$pengurus->warga_id = $warga->warga_id;
			$pengurus->pengurus_jabatan = $pengurus_jabatan;
			$pengurus->mk_id = $mk->mk_id;
			$pengurus->save();

			// set data user
			$verify_code = mt_rand(100000, 999999);
			$user->user_ref_id = $warga->warga_id;
			$user->user_type = 2; // pengurus
			$user->user_status = 1; // admin
			$user->user_email = $email;
			$user->user_password = md5($password);
			$user->remember_token = bin2hex(random_bytes(64));
			$user->verify_code = $verify_code;
			$user->active_status = 1;
			$user->fcm_token = $fcm_token;
			// save
			$user->save();

			//update kode wilayah
			//
			$random_angka = '0123456789';
	        //
	        $today = Carbon::now()->format('l');
	        $name_of_day = get_day_name($today); // Huruf pertama hari pendaftaran
	        $two_number_random = substr(str_shuffle(str_repeat($random_angka, 5)), 0, 2);;// Dua digit random Angka
	        $one_number_random = substr(str_shuffle(str_repeat($random_angka, 5)), 0, 1);;// Satu digit random Angka
	        $idWilayah = $last_wil_id;
			$id_wil = get_huruf_by_kode($idWilayah);
			$kd = implode("", $id_wil);
			$kodeWilayah = $name_of_day.''.$two_number_random.''.$kd.''.$one_number_random;
			//
			$wilayah_update = Wilayah::find($last_wil_id);
			$wilayah_update->wil_kode = $kodeWilayah;
			$wilayah_update->save();

			//send email

			$to_name 	= "Halo ".$warga_nama_depan."".$warga_nama_belakang;
	        $to_email 	= $warga_email;

	        $appUrl = 'https://play.google.com/store/apps/details?id=com.rukun.app';
	        $urlAplikasi = $appUrl;

	        $data = array(
	        	'warga_email' 	=> $warga_email,
	        	'warga_password' 	=> $password,
	        	'warga_nama' 	=> $warga_nama_depan,
	            'nama_wilayah' 	=> $wil_nama,
	            'url_aplikasi' 	=> $urlAplikasi,
	        );

	        Mail::send('emails.reg-wilayah', $data, function($message) use ($to_name, $to_email) {
	            $message->to($to_email, $to_name)
	                    ->subject('[Rukun] Berhasil Registrasi Wilayah di Aplikasi Rukun');
	            $message->from(ENV('MAIL_FROM_ADDRESS'),'Rukun');
	        });

			$results = array(
				"name" => $warga_nama_depan,
				"email" => $email,
				"phone" => $warga_hp,
				"wil_id" => $last_wil_id,
				"wil_kode" => $wil_kode
			);

			// response
			$response['status'] = "success";
			$response['message'] = "OK";
			$response['results'] = $results;

			// return json response
			return response()->json($response);
			//
		}
		catch(\Exception $e)
	    {
	    	//delete wilayah pendaftaran terkait

	        //------------------CURL-----------------------
	        $url          = ENV('APP_URL')."/api/wilayah/delete";
	        $method       = "POST";
	        $post_field = array(
            	'wil_id' 	=> $last_wil_id
            );
	        //---------------------------------------------
	        $res_ = set_curl_delete($url,$method,$post_field);

	      	// response
			$response['status'] = "error";
			$response['message'] = 'Proses pendaftaran wilayah tidak berhasil, silahkan diulangi kembali. Terima kasih';
			$response['results'] =  $e->getMessage();
			// return json response
			return response()->json($response);
	    }

	}

	// Regsiter Warga
	public function register_warga(Request $request, Wilayah $wilayah)

	{
		try
        {
            // get param
            $wil_kode = Str::upper($request->wil_kode);
            // get wil_id
            $res_wil = $wilayah->get_wilayah_by_kode($wil_kode);
            // login
            $password = $request->password;
            $email = $request->wt_email;

            // account warga
            $wt_nama_depan = $request->wt_nama_depan;
            $wt_nama_belakang = $request->wt_nama_belakang;
            $wt_hp = $request->wt_hp;
            $wt_email = $request->wt_email;
            $wt_alamat = $request->wt_alamat;
            $wt_no_rumah = $request->wt_no_rumah;
            $kb_id = $request->kb_id;
            $wil_id = $res_wil->wil_id;
            $wil_nama = $res_wil->wil_nama;

            // validate param
            if($wt_nama_depan=='' ||  $wt_email=='')
            {

                // response
				$response['status'] = "error";
				$response['message'] = "Warga name, email are required fields";

				// return json response
				return response()->json($response);
            }

            // validate email
            $user = User::where('user_email',$email)->first();
            if(!empty($user))
            {

                // response
				$response['status'] = "error";
				$response['message'] = "Email : $email sudah terdaftar";

				// return json response
				return response()->json($response);
            }

            // validate hp diwarga
            $warga = Warga::where('warga_hp',$wt_hp)->first();
            if(!empty($warga))
            {

                // response
				$response['status'] = "error";
				$response['message'] = "Nomor HP : $wt_hp sudah terdaftar";

				// return json response
				return response()->json($response);
            }

            // validate email diwarga
            $warga_email = Warga::where('warga_email',$wt_email)->first();
            if(!empty($warga_email))
            {

                // response
				$response['status'] = "error";
				$response['message'] = "Nomor HP : $wt_hp sudah terdaftar";

				// return json response
				return response()->json($response);
            }

            // validate hp diwarga temp
            $wtemp_hp = WargaTemp::where('wt_hp',$wt_hp)->first();
            if(!empty($wtemp_hp))
            {

                // response
				$response['status'] = "error";
				$response['message'] = "Nomor HP : $wt_hp sudah terdaftar";

				// return json response
				return response()->json($response);
            }

            // validate email diwarga
            $wtemp_email = WargaTemp::where('wt_email',$wt_email)->first();
            if(!empty($wtemp_email))
            {

                // response
				$response['status'] = "error";
				$response['message'] = "Nomor HP : $wt_hp sudah terdaftar";

				// return json response
				return response()->json($response);
            }


            $pengurus = new Pengurus;
            $warga_temp = new WargaTemp;
            $user = new User;

            // set data warga
            $warga_temp->wt_nama_depan = $wt_nama_depan;
            $warga_temp->wt_nama_belakang = $wt_nama_belakang;
            $warga_temp->wt_hp = $wt_hp;
            $warga_temp->wt_email = $wt_email;
            $warga_temp->wt_alamat = $wt_alamat;
            $warga_temp->wt_no_rumah = $wt_no_rumah;
            $warga_temp->kb_id = $kb_id;
            $warga_temp->wil_id = $wil_id;
            // save
            $warga_temp->save();
            $wt_id = $warga_temp->wt_id;
            // set data user
            $verify_code = mt_rand(100000, 999999);
            $user->user_temp_id = $warga_temp->wt_id;
            $user->user_type = 3; //bukan pengurus
            $user->user_email = $email;
            $user->user_password = md5($password);
            $user->remember_token = bin2hex(random_bytes(64));
            $user->verify_code = $verify_code;
            $user->user_status = 0; // bukan admin
            $user->active_status = 0;
            $user->fcm_token = '0';
            // save
            $user->save();


            /*== Notif Register Success ==*/
            $pengurus = $pengurus->get_list_pengurus($wil_id);

            //check
            if(empty($pengurus))
            {

                // response
				$response['status'] = "error";
				$response['message'] = "Pengurus belum ditambahkan";

				// return json response
				return response()->json($response);

            }else{

                //send to user peegurus
                $endpoint = "https://fcm.googleapis.com/fcm/send";
                $client = new \GuzzleHttp\Client();
                //

                //print_r($pengurus);

                foreach ($pengurus as $rows) {

                    $fcm_token = $rows->fcm_token;
                    $wt_nama = ucfirst($wt_nama_depan).' '.ucfirst($wt_nama_belakang);

                    //create json data
                    $data_json = [
                            'notification' => [
                                'title' => ''.$wt_nama.' Sukses Mendaftar',
                                'body' => 'HP:'.$wt_hp.' Alamat:'.$wt_alamat.'Email:'.$wt_email,
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                'sound' => 'alarm.mp3'
                            ],
                            'data' => [
                                'id' => ''.$wt_id.'',
                                'page' => 'register'
                            ],
                            'to' => ''.$fcm_token.''
                        ];

                    $requestAPI = $client->post( $endpoint, [
                        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                        'body' => json_encode($data_json)
                    ]);

                }
            }


            $to_name = $wt_nama_depan.' '.$wt_nama_belakang;
            $to_email = $wt_email;
            $data = array(
                'nama_warga'=> $to_name,
                'wil_nama' => $wil_nama
            );

            Mail::send('emails.mail-register-warga', $data, function($message) use ($to_name, $to_email) {
                $message->to($to_email, $to_name)
                        ->subject('[Rukun] Registrasi Rukun Berhasil');
                $message->from(ENV('MAIL_FROM_ADDRESS'),'Rukun');
            });

            // response
			$response['status'] = "success";
			$response['message'] = "Terima Kasih Anda berhasil daftar dirukun, silahkan periksa email Anda.";

			// return json response
			return response()->json($response);
        }
        catch(\Exception $e)
        {

            // response
			$response['status'] = "error";
			$response['message'] = "Proses pendaftaran tidak berhasil, silahkan diulangi kembali. Terima kasih";
			$response['results'] =  $e->getMessage();

			// return json response
			return response()->json($response);
        }
	}


	public function sample_delete_wil(Request $request)

	{
			// $wil_id = $request->wil_id;
			// //delete wilayah pendaftaran terkait

	  //       //------------------CURL-----------------------
	  //       $url          = ENV('APP_URL')."/api/wilayah/delete";
	  //       $method       = "POST";
	  //       $post_field = array(
   //          	'wil_id' 	=> $wil_id
   //          );
	  //       //---------------------------------------------
	  //       $res_ = set_curl_delete($url,$method,$post_field);

	}


	public function create_mk(Request $request, Warga $warga)
	{
		try
    	{
    		//find wil_id by email
    		$email = $request->email;
    		$warga = $warga->get_detail_email($email);
    		//
			$wil_id = $warga->wil_id;
			$mk_periode_mulai = $request->mk_periode_mulai;
			$mk_periode_akhir = $request->mk_periode_akhir;

			$mk = new Mk;

			$mk->wil_id = $wil_id;
			$mk->mk_periode_mulai = $mk_periode_mulai;
			$mk->mk_periode_akhir = $mk_periode_akhir;
			$mk->mk_status = 1;

			$mk->save();

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

	public function create_pengurus(Request $request, Warga $warga)
	{
		try
    	{
    		$pengurus_jabatan = $request->pengurus_jabatan;
    		//find wil_id by email
    		$email = $request->email;
    		$warga = $warga->get_detail_email_mk($email);
    		//
			$warga_id = $warga->warga_id;
			$mk_id = $warga->mk_id;


			$pengurus = new Pengurus;

			$pengurus->warga_id = $warga_id;
			$pengurus->mk_id = $mk_id;
			$pengurus->pengurus_jabatan = $pengurus_jabatan;

			$pengurus->save();

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

	public function create_kb(Request $request, Warga $warga)
	{
		try
    	{
    		$kb_keterangan = $request->kb_keterangan;
    		$kb_tarif_ipl = $request->kb_tarif_ipl;

    		//find wil_id by email
    		$email = $request->email;
    		$warga = $warga->get_detail_email($email);
    		//
			$wil_id = $warga->wil_id;


			$kb = new Kb;


			$kb->wil_id = $wil_id;
			$kb->kb_keterangan = $kb_keterangan;
			$kb->kb_tarif_ipl = $kb_tarif_ipl;
			$kb->save();

			//update kb
			$warga = Warga::find($warga->warga_id);
			$warga->kb_id = $kb->kb_id;
			$warga->save();

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
	public function create_kb_portal(Request $request, Warga $warga)
	{
		try
    	{
    		$kb_keterangan = $request->kb_keterangan;
    		$kb_tarif_ipl =	str_replace(",", ".", str_replace(".", "", $request->kb_tarif_ipl));

    		//find wil_id by email
    		$email = $request->email;
    		$warga = $warga->get_detail_email($email);
    		//
			$wil_id = $warga->wil_id;


			$kb = new Kb;


			$kb->wil_id = $wil_id;
			$kb->kb_keterangan = $kb_keterangan;
			$kb->kb_tarif_ipl = $kb_tarif_ipl;
			$kb->save();

			//update kb
			$warga = Warga::find($warga->warga_id);
			$warga->kb_id = $kb->kb_id;
			$warga->save();

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

	public function tambah_crm(Request $request)
	{
		// get param
		// login
		$wil_id = $request->wil_id;
		$password = $request->password;
		$email = $request->warga_email;

		// account warga
		$warga_nama_depan = $this->getFirstName($request->warga_nama);
		$warga_nama_belakang = $this->getLastName($request->warga_nama);
		$warga_hp = $request->warga_hp;
		$warga_email = $request->warga_email;
		$warga_alamat = $request->warga_alamat;
		$warga_no_rumah = $request->warga_no_rumah;
		$warga_tgl_lahir = $request->warga_tgl_lahir;
		$warga_status = $request->warga_status;
		$warga_status_rumah = $request->warga_status_rumah;


		// validate param
		if($warga_nama_depan=='' || $warga_hp=='' || $warga_email=='')
		{
			$response['status'] = "error";
			$response['message'] = "Warga name, email, hp are required fields";
			return response()->json($response);
			exit();
		}
		// validate email
		$user = User::where('user_email',$email)->first();
		if(!empty($user))
		{
			$response['status'] = "error";
			$response['message'] = "Email : $email has been registered";
			return response()->json($response);
			exit();
		}

		$warga = new Warga;
		$user = new User;
		$kb = new Kb;

		$kb->wil_id = $wil_id;
		$kb->kb_keterangan = 21;
		$kb->kb_tarif_ipl = 0;
		// save
		$kb->save();

		// set data warga
		$warga->warga_nama_depan = $warga_nama_depan;
		$warga->warga_nama_belakang = $warga_nama_belakang;
		$warga->warga_hp = $warga_hp;
		$warga->warga_email = $warga_email;
		$warga->warga_alamat = $warga_alamat;
		$warga->warga_no_rumah = $warga_no_rumah;
		$warga->warga_tgl_lahir = $warga_tgl_lahir;
		$warga->wil_id = $wil_id;
		$warga->warga_status = $warga_status;
		$warga->warga_status_rumah = $warga_status_rumah;
		$warga->kb_id = $kb->kb_id;
		// save
		$warga->save();


		// set data user
		$verify_code = mt_rand(100000, 999999);
		$user->user_ref_id = $warga->warga_id;
		$user->user_type = 2; // pengurus
		$user->user_email = $email;
		$user->user_password = md5($password);
		$user->remember_token = bin2hex(random_bytes(64));
		$user->verify_code = $verify_code;
		$user->active_status = 1;
		//$user->fcm_token = $fcm_token;
		// save
		$user->save();



		$results = array(
			"name" => $warga_nama_depan,
			"email" => $email,
			"phone" => $warga_hp,
			"wil_id" => $wil_id
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);

	}


	/*== Sign In ==*/
	public function signin(Request $request, Warga $warga)
	{
		// get param
		$password = $request->password;
		$email = $request->email;

		// validate param
		if($password=='' || $email=='')
		{
			$response['status'] = "error";
			$response['message'] = "Email & password are required fields";
			return response()->json($response);
			exit();
		}

		// validate

        $user = User::where('user_email',$email)->first();
        if(empty($user)) {

            //jika email tidak ditemukan cari nomor hp di warga
            $warga = Warga::where('warga_hp',$email)->first();
            if(empty($warga)) {
                $response['status'] = "error";
                $response['message'] = "Email/HP atau Password tidak sesuai";
                return response()->json($response);
            }else {
                $user = User::where('user_ref_id',$warga->warga_id)->first();
                if(empty($user)) {
                    $response['status'] = "error";
                    $response['message'] = "Email/HP atau Password tidak sesuai";
                    return response()->json($response);
                }
            }

        }

        if($user->user_password != md5($password)) {
            $response['status'] = "error";
            $response['message'] = "Email/HP atau Password tidak sesuai";
            return response()->json($response);
        }

        if($user->active_status==0) {
			$response['status'] = "error";
			$response['message'] = "Email/HP belum diaktifasi";
			return response()->json($response);
			exit();
		}

		$warga = $warga->get_detail_signin($user->user_ref_id);
		$wil = Wilayah::find($warga->wil_id);

		//
		$_trial = GlobalVariable::where('global_name','trial')->first();
        $_retensi = GlobalVariable::where('global_name','retensi')->first();

		$wil_mulai_trial = Carbon::parse($wil->wil_mulai_trial)->isoFormat('D MMMM Y');
        $wil_expired_trial = Carbon::parse($wil->wil_mulai_trial)->addDays($_trial->global_value)->isoFormat('D MMMM Y');
        $wil_retensi_trial = Carbon::parse($wil->wil_retensi_trial)->isoFormat('D MMMM Y');
        $wil_end_retensi = Carbon::parse($wil->wil_retensi_trial)->addDays($_retensi->global_value)->isoFormat('D MMMM Y');

		$results = array(
			"wil_id" => $warga->wil_id,
			"warga_id" => $warga->warga_id,
			"warga_nama_depan" => $warga->warga_nama_depan,
			"warga_nama_belakang" => $warga->warga_nama_belakang,
			"warga_hp" => $warga->warga_hp,
			"warga_email" => $warga->warga_email,
			"warga_status" => $warga->warga_status,
			"warga_status_rumah" => $warga->warga_status_rumah,
            "warga_alamat" => $warga->warga_alamat." ".$warga->warga_no_rumah,
			"wil_name" => $wil->wil_nama,
			"wil_status" => $wil->wil_status,
			"wil_mulai_trial" => $wil_mulai_trial,
			"wil_expired_trial" => $wil_expired_trial,
			"wil_retensi_trial" => $wil_retensi_trial,
            "wil_end_retensi" => $wil_end_retensi,
			"wil_mulai_langganan" => $wil->wil_mulai_langganan,
			"wil_expired" => $wil->wil_expired,
			"wil_tag_due" => $wil->wil_tag_due,
			"user_type" => $warga->user_type,
			"user_status" => $warga->user_status,
			"fcm_token" => $user->fcm_token,
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*== Undang ==*/
	public function undang_hp(Request $request, Wu $wu)
	{
		//get param
		$warga_email = '-';
		$warga_hp = $request->hp;
		//$warga_nama_depan = $request->warga_nama_depan;
		$wil_id = $request->wil_id;
		$warga_id = $request->warga_id;

		// validate email
		$wu = Wu::where('undang_hp',$warga_hp)->first();
		if(!empty($wu))
		{
			$response['status'] = "error";
			$response['message'] = "Nomor HP : $warga_hp sudah diundang";
			return response()->json($response);
			exit();
		}

		//entry warga undang
		$wu = new Wu;

		$wu->wil_id = $wil_id;
		$wu->warga_id = $warga_id;
		$wu->undang_email = $warga_email;
		$wu->undang_hp = $warga_hp;
		$wu->status = '0';
		// save
		$wu->save();


        //send message
		$undang_id = $wu->undang_id;
		$mainUrl = env("APP_URL").'/warga/register-h/'.encrypt($undang_id).'/'.encrypt($wil_id);
        $urlRegistrasi = Bitly::getUrl($mainUrl);

        $appUrl = 'https://play.google.com/store/apps/details?id=com.rukun.app';
        $urlAplikasi = $appUrl;

        $wilayah = Wilayah::find($wil_id);
        $pengurus = Warga::find($warga_id);
        $namaPengurus = $pengurus->warga_nama_depan." ".$pengurus->warga_nama_belakang;

        $no_hp = $warga_hp;
        $message = "Halo Warga ". $wilayah->wil_nama.",";
        $message .= "\n\nAnda telah diundang oleh *".$namaPengurus."* untuk bergabung pada Aplikasi Rukun.";
        $message .= "\n\nSilahkan klik tautan Registrasi berikut untuk melakukan registrasi Aplikasi, kemudian install Aplikasi Rukun pada perangkat telepon pintar Anda.";
        $message .= "\n*_Undangan ini hanya berlaku bagi Anda dan tidak berlaku bagi orang lain._*";
        $message .= "\n\n".$urlRegistrasi;
        $message .= "\n\nAplikasi Rukun dapat Anda install melalui tautan berikut : ";
        $message .= "\n\n".$urlAplikasi;
        $message .= "\n\n_Pesan ini dikirim melalui akun Wilayah ".$wilayah->wil_nama." pada Aplikasi Rukun_";

        SendPhoneMessage::whatsAppMessaging($no_hp, $message);

		// response
		$response['status'] = "success";
		$response['message'] = "Undang Warga Berhasil";

		// return json response
		return response()->json($response);
	}

	/*== Undang Email == */
	public function undang_email(Request $request, Wu $wu)
	{
		//get param
		$warga_email = $request->email;
		$warga_hp = '-';
		//$warga_nama_depan = $request->warga_nama_depan;
		$wil_id = $request->wil_id;
		$warga_id = $request->warga_id;

		// validate email
		$wu = Wu::where('undang_email',$warga_email)->first();
		if(!empty($wu))
		{
			$response['status'] = "error";
			$response['message'] = "Email : $warga_email sudah diundang";
			return response()->json($response);
			exit();
		}

		//entry warga undang
		$wu = new Wu;

		$wu->wil_id = $wil_id;
		$wu->warga_id = $warga_id;
		$wu->undang_email = $warga_email;
		$wu->undang_hp = $warga_hp;
		$wu->status = '0';
		// save
		$wu->save();


		$undang_id = $wu->undang_id;
        //send via email

        $wilayah = Wilayah::find($wil_id);
        $pengurus = Warga::find($warga_id);
        $namaPengurus = $pengurus->warga_nama_depan." ".$pengurus->warga_nama_belakang;
        $mainUrl = env("APP_URL").'/warga/register-m/'.encrypt($undang_id).'/'.encrypt($wil_id);
        $appUrl = 'https://play.google.com/store/apps/details?id=com.rukun.app';
        $urlAplikasi = $appUrl;

        $to_name = "Warga ".$wilayah->wil_nama;
        $to_email = $warga_email;

        $data = array(
            'nama_wilayah' => $wilayah->wil_nama,
            'nama_pengurus' => $namaPengurus,
            'url_registrasi' => $mainUrl,
            'url_aplikasi' => $urlAplikasi,
        );

        Mail::send('emails.mail-new', $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                    ->subject('Undangan bergabung dengan Aplikasi Rukun');
            $message->from('rukun.id.99@gmail.com','Rukun');
        });

		// response
		$response['status'] = "success";
		$response['message'] = "Undang Warga Berhasil";

		// return json response
		return response()->json($response);
	}

	/*== Change Email Verification ==*/
	public function email_verify(Request $request)
	{
		$warga_id = $request->warga_id;
		$warga_email = $request->warga_email;

		//send email
		$verify_code = rand(1000,1111);

		//set to user
		$user = User::where(['user_ref_id'=>$warga_id])->first();
		$user->verify_code = $verify_code;
		$user->save();

		//
		$message = "Halo";
        $message .= "\n\nSilahkan masukan kode verifikasi berikut untuk mengganti Email Anda *".$verify_code."*";


        dispatch(new App\Jobs\SendMailResetEmail($warga_email));

		// response
		$response['status'] = "success";
		$response['message'] = "Verify Berhasil";

		// return json response
		return response()->json($response);
	}

	/*== Change HP Verification ==*/
	public function hp_verify(Request $request)
	{
		$warga_id = $request->warga_id;
		$warga_hp = $request->warga_hp;

		// send notif whatsapp
		$verify_code = rand(1000,1111);

		//set to user
		$user = User::where(['user_ref_id'=>$warga_id])->first();
		$user->verify_code = $verify_code;
		$user->save();

		$message = "Halo";
        $message .= "\n\nSilahkan masukan kode verifikasi berikut untuk mengganti Nomor HP Anda *".$verify_code."*";

		SendPhoneMessage::whatsAppMessaging($warga_hp, $message);

		// response
		$response['status'] = "success";
		$response['message'] = "Verify Berhasil";

		// return json response
		return response()->json($response);
	}

	public function setujui(Request $request) {

		try
		{
			$warga_id = $request->warga_id;
			//
			$warga = Warga::find($warga_id);
			$warga->warga_status_aktif = '1';
			$warga->save();

			// response
			$response['status'] = "success";
			$response['message'] = "Warga sudah disetujui";
			$response['results'] = $warga;
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Silahkan dicoba kembali";
			return response()->json($response);
			exit();
		}

		// return json response
		return response()->json($response);
	}


	/*== Update ==*/
	public function update(Request $request)
	{
		// account warga
		$warga_id = $request->warga_id;
		$warga_email = $request->email;
		$warga_nama_depan = $request->warga_nama_depan;
		$warga_nama_belakang = $request->warga_nama_belakang;
		$warga_hp = $request->warga_hp;
		$warga_alamat = $request->warga_alamat;
		$warga_no_rumah = $request->warga_no_rumah;
		$warga_geo = $request->warga_geo;
		$warga_status = $request->warga_status;
		$warga_status_rumah = $request->warga_status_rumah;
		$warga_tgl_lahir = $request->warga_tgl_lahir;
		$kb_id = $request->kb_id;
		$password = $request->password;

		// get data
		$warga = Warga::find($warga_id);
		$user = User::where('user_ref_id', $warga_id)->first();

        $wil_id = $warga->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		// validate email
		if($warga_email != '') {
			if($user->user_email != $warga_email) // update email baru
			{
				$cek_user = User::where('user_email',$warga_email)->first();
				if(!empty($cek_user))
				{
					$response['status'] = "error";
					$response['message'] = "Email : $warga_email sudah terdaftar";
					return response()->json($response);
				}else {
                    // set data user
                    $user->user_email = $warga_email;
                    $user->updated_at = Carbon::now();
                    // save
                    $user->save();
                }
			}
        }

		// set data warga
		if($warga_nama_depan!='')
			$warga->warga_nama_depan = $warga_nama_depan;
		if($warga_hp!='') {
            $itemWarga = Warga::where('warga_hp',$warga_hp)
                        ->where("warga_id",'!=',$warga_id)->first();
            if(empty($itemWarga)) {
                $warga->warga_hp = $warga_hp;
            }else {
                $response['status'] = "error";
				$response['message'] = "No.HP ".$warga_hp." sudah terdaftar";
                return response()->json($response);
            }
        }
		if($warga_nama_belakang!='')
			$warga->warga_nama_belakang = $warga_nama_belakang;
		if($warga_email!='')
			$warga->warga_email = $warga_email;
		if($warga_alamat!='')
			$warga->warga_alamat = $warga_alamat;
		if($warga_no_rumah!='')
			$warga->warga_no_rumah = $warga_no_rumah;
		if($warga_geo!='')
			$warga->warga_geo = $warga_geo;
		if($warga_status!='')
			$warga->warga_status = $warga_status;
		if($warga_status_rumah!='')
			$warga->warga_status_rumah = $warga_status_rumah;
		if($warga_tgl_lahir!='')
			$warga->warga_tgl_lahir = $warga_tgl_lahir;
		if($kb_id!='')
			$warga->kb_id = $kb_id;

		// save
        $warga->warga_id = $warga_id;
		$warga->save();

        if($password != '') {
            $user->user_password = md5($password);
            $user->updated_at = Carbon::now();
            $user->save();
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $warga;

		// return json response
		return response()->json($response);
	}
	public function update_crm(Request $request)
	{
		// account warga
		$warga_id = $request->warga_id;
		$warga_email = $request->warga_email;
		$warga_nama_depan = $this->getFirstName($request->warga_nama);
		$warga_nama_belakang = $this->getLastName($request->warga_nama);
		$warga_hp = $request->warga_hp;
		$warga_alamat = $request->warga_alamat;
		$warga_no_rumah = $request->warga_no_rumah;
		$warga_geo = $request->warga_geo;
		$warga_status = $request->warga_status;
		$warga_status_rumah = $request->warga_status_rumah;
		$warga_tgl_lahir = $request->warga_tgl_lahir;
		//$kb_id = $request->kb_id;

		// // validate param
		// if($warga_nama_depan=='' || $warga_hp=='')
		// {
		// 	$response['status'] = "error";
		// 	$response['message'] = "Warga name, hp are required fields";
		// 	return response()->json($response);
		// 	exit();
		// }

		// get data
		$warga = Warga::find($warga_id);
		$user = User::where(['user_ref_id'=>$warga_id])->first();

		// validate email
		if($user->user_email != $warga_email) // update email baru
		{
			$cek_user = User::where('user_email',$warga_email)->first();
			if(!empty($cek_user))
			{
				$response['status'] = "error";
				$response['message'] = "Email : $warga_email has been registered";
				return response()->json($response);
				exit();
			}
		}

		// set data warga
		if($warga_nama_depan!='')
			$warga->warga_nama_depan = $warga_nama_depan;
		if($warga_hp!='')
			$warga->warga_hp = $warga_hp;
		if($warga_nama_belakang!='')
			$warga->warga_nama_belakang = $warga_nama_belakang;
		if($warga_email!='')
			$warga->warga_email = $warga_email;
		if($warga_alamat!='')
			$warga->warga_alamat = $warga_alamat;
		if($warga_no_rumah!='')
			$warga->warga_no_rumah = $warga_no_rumah;
		if($warga_geo!='')
			$warga->warga_geo = $warga_geo;
		if($warga_status!='')
			$warga->warga_status = $warga_status;
		if($warga_status_rumah!='')
			$warga->warga_status_rumah = $warga_status_rumah;
		if($warga_tgl_lahir!='')
			$warga->warga_tgl_lahir = $warga_tgl_lahir;
		//if($kb_id!='')
		//	$warga->kb_id = $kb_id;

		// save
		$warga->save();

		// set data user
		$user->user_email = $warga_email;
		//if($password!='')
		//	$user->user_password = md5($password);
		$user->updated_at = date("Y-m-d H:i:s");
		// save
		$user->save();

		$results = array(
			"warga_nama_depan" => $warga->warga_nama_depan,
			"warga_email" => $warga->warga_email,
			"warga_hp" => $warga->warga_hp
		);
		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;


		// return json response
		return response()->json($response);
	}
	public function add_crm(Request $request)
	{
		// account warga
		$warga_id = $request->warga_id;
		$warga_email = $request->warga_email;
		$warga_nama_depan = $request->warga_nama_depan;
		$warga_nama_belakang = $request->warga_nama_belakang;
		$warga_hp = $request->warga_hp;
		$warga_alamat = $request->warga_alamat;
		$warga_no_rumah = $request->warga_no_rumah;
		$warga_geo = $request->warga_geo;
		$warga_status = $request->warga_status;
		$warga_status_rumah = $request->warga_status_rumah;
		$warga_tgl_lahir = $request->warga_tgl_lahir;
		//$kb_id = $request->kb_id;

		// // validate param
		// if($warga_nama_depan=='' || $warga_hp=='')
		// {
		// 	$response['status'] = "error";
		// 	$response['message'] = "Warga name, hp are required fields";
		// 	return response()->json($response);
		// 	exit();
		// }

		// get data
		$warga = Warga::find($warga_id);
		$user = User::where(['user_ref_id'=>$warga_id])->first();

		// validate email
		if($user->user_email != $warga_email) // update email baru
		{
			$cek_user = User::where('user_email',$warga_email)->first();
			if(!empty($cek_user))
			{
				$response['status'] = "error";
				$response['message'] = "Email : $warga_email has been registered";
				return response()->json($response);
				exit();
			}
		}

		// set data warga
		if($warga_nama_depan!='')
			$warga->warga_nama_depan = $warga_nama_depan;
		if($warga_hp!='')
			$warga->warga_hp = $warga_hp;
		if($warga_nama_belakang!='')
			$warga->warga_nama_belakang = $warga_nama_belakang;
		if($warga_email!='')
			$warga->warga_email = $warga_email;
		if($warga_alamat!='')
			$warga->warga_alamat = $warga_alamat;
		if($warga_no_rumah!='')
			$warga->warga_no_rumah = $warga_no_rumah;
		if($warga_geo!='')
			$warga->warga_geo = $warga_geo;
		if($warga_status!='')
			$warga->warga_status = $warga_status;
		if($warga_status_rumah!='')
			$warga->warga_status_rumah = $warga_status_rumah;
		if($warga_tgl_lahir!='')
			$warga->warga_tgl_lahir = $warga_tgl_lahir;
		//if($kb_id!='')
		//	$warga->kb_id = $kb_id;

		// save
		$warga->save();

		// set data user
		$user->user_email = $warga_email;
		//if($password!='')
		//	$user->user_password = md5($password);
		$user->updated_at = date("Y-m-d H:i:s");
		// save
		$user->save();

		$results = array(
			"warga_nama_depan" => $warga->warga_nama_depan,
			"warga_email" => $warga->warga_email,
			"warga_hp" => $warga->warga_hp
		);
		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;


		// return json response
		return response()->json($response);
	}

	/*== Update Foto ==*/
	public function update_foto(Request $request)
	{
		$warga_id = $request->warga_id;
		$warga_foto = $request->file('warga_foto');

		$warga = Warga::find($warga_id);

        $wil_id = $warga->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }


        if($warga->warga_foto != 'default.png' and !empty($warga->warga_foto))
            File::delete(public_path('img/pp/').$warga->warga_foto);

		// upload img
		if($warga_foto!='')
		{
			// destination path
			$destination_path = public_path('img/pp/');
			$img = $warga_foto;

			// upload
			$md5_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

			// resize photo
			$img = Image::make(URL("public/img/pp/$md5_name.$ext"));
			$img->fit(500);
			$img->save(public_path("img/pp/$md5_name.$ext"));

			// set data
			$warga->warga_foto = $img_file;

		}else{
			// set data
			$warga->warga_foto = 'default.png';
		}

		// save
		$warga->save();
		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}

	/*== Update Password ==*/
	public function update_password(Request $request)
	{
		// account warga
		$password_old = md5($request->password_old);
		$password_new = $request->password_new;
		$warga_id = $request->warga_id;

        $warga = Warga::find($warga_id);
        $wil_id = $warga->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		$user = User::where(['user_ref_id'=>$warga_id, 'user_password' => $password_old])->first();

		if(empty($user))
		{
			$response['status'] = "error";
			$response['message'] = "Password lama salah, silahkan coba lagi.";
			return response()->json($response);
		}

        if($user->user_password == md5($password_new)) {
            $response['status'] = "error";
			$response['message'] = "Password baru tidak boleh sama dengan password lama.";
			return response()->json($response);
        }

		// set data user
		if($password_new!='')
			$user->user_password = md5($password_new);
		$user->updated_at = date("Y-m-d H:i:s");
		// save
		$user->save();

		// response
		$response['status'] = "success";
		$response['message'] = "Ubah Password Berhasil";

		// return json response
		return response()->json($response);
	}

	/*== Update Admin ==*/
	public function update_admin(Request $request)
	{
		$warga_id_lama = $request->warga_id_lama;
		$warga_id_baru = $request->warga_id_baru;

		// get data
		$user_lama = User::where(['user_ref_id'=>$warga_id_lama])->first();
		$user_baru = User::where(['user_ref_id'=>$warga_id_baru])->first();


		$user_lama->user_type = 3;
		$user_baru->user_type = 2;
		// save
		$user_lama->save();
		$user_baru->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		// return json response
		return response()->json($response);

	}

	/*== Update token ==*/
	public function update_token(Request $request)
	{

		//get param
		$warga_id = $request->warga_id;
		$fcm_token = $request->fcm_token;

		$user = User::where(['user_ref_id'=>$warga_id])->first();

		//print($user);

		//save
		$user->fcm_token = $fcm_token;
		$user->save();

		//response
		$response['status'] = "success";
		$response['message'] = "OK";
		// return json response
		return response()->json($response);
	}

	/*== Reset Password ==*/
	public function lupa_password(Request $request)
	{
		$emailnohp = $request->emailnohp;
		// check email/no. hp
		if(filter_var($emailnohp, FILTER_VALIDATE_EMAIL)) {
			//$_status = '0';
			$user = Warga::where(['warga_email'=>$emailnohp])->first();
			//
			if(empty($user))
			{
				$response['status'] = "error";
				$response['message'] = "Email tidak ada";
				return response()->json($response);
				exit();
			}
			//
			$message_1 = "Anda sudah meminta untuk mengganti kata sandi, abaikan pesan ini jika bukan Anda.";
			$message_2 = "Silahkan klik tombol Reset Kata Sandi berikut untuk mengisi kata sandi baru Anda. Terima Kasih.";

			$warga_nama_depan = $user->warga_nama_depan;
			$warga_email = $user->warga_email;
			$warga_id = $user->warga_id;
			$wil_id = $user->wil_id;

			$to_name = $warga_nama_depan;
			$to_email = $warga_email;
			$data = array(
				'name'=> $warga_nama_depan,
				'message_1' => $message_1,
				'message_2' => $message_2,
				'warga_id'	=> encrypt($warga_id),
				'wil_id'	=> encrypt($wil_id),
			);
			//
			Mail::send('emails.reset', $data, function($message) use ($to_name, $to_email) {
		    	$message->to($to_email, $to_name)
		            ->subject('[Rukun] Reset Kata Sandi');
		   		 $message->from('rukun.id.99@gmail.com','Rukun');
			});

			// response
			$response['status'] = "success";
			$response['message'] = "Link reset kata sandi sudah dikirim ke email. Silahkan cek email Anda.";

			// return json response
			return response()->json($response);

		}elseif(is_numeric($emailnohp)){
			// $_status = '1';
			$user = Warga::where(['warga_hp'=>$emailnohp])->first();
			//
			if(empty($user))
			{
				$response['status'] = "error";
				$response['message'] = "No. HP tidak ada";
				return response()->json($response);
				exit();
			}

			$message_1 = "Anda sudah meminta untuk mengganti kata sandi, abaikan pesan ini jika bukan Anda.";
			$message_2 = "Silahkan klik tombol Reset Kata Sandi berikut untuk mengisi kata sandi baru Anda. Terima Kasih.";

			$warga_nama_depan = $user->warga_nama_depan;
			$warga_email = $user->warga_email;
			$warga_id = $user->warga_id;
			$wil_id = $user->wil_id;

			$to_name = $warga_nama_depan;
			$to_email = $warga_email;
			$data = array(
				'name'=> $warga_nama_depan,
				'message_1' => $message_1,
				'message_2' => $message_2,
				'warga_id'	=> encrypt($warga_id),
				'wil_id'	=> encrypt($wil_id),
			);
			//
			Mail::send('emails.reset', $data, function($message) use ($to_name, $to_email) {
		    	$message->to($to_email, $to_name)
		            ->subject('[Rukun] Reset Kata Sandi');
		   		 $message->from('rukun.id.99@gmail.com','Rukun');
			});

			// response
			$response['status'] = "success";
			$response['message'] = "Link reset kata sandi sudah dikirim ke email. Silahkan cek email Anda.";

			// return json response
			return response()->json($response);
		}else{
			// response
			$response['status'] = "success";
			$response['message'] = "Email/HP tidak ada";

			// return json response
			return response()->json($response);
		}



	}


	/*== Reset Account Email ==*/
	public function reset($email, Request $request, Warga $warga, User $user)
	{
		if($email=='')
		{
			$response['status'] = "error";
			$response['message'] = "email is required fields";
			return response()->json($response);
			exit();
		}

		//
		// get data
		$warga = $warga->get_detail_by_email($email);
		//
		$warga_id = $warga->warga_id;
		$wil_id = $warga->wil_id;
		// print_r($email);
		// core user delete
		$user = $user->get_detail_warga($warga_id);
		$user_id = $user->user_id;

		// print_r($wil_id);

		try
		{
			// delete
			User::find($user_id)->delete();
			Warga::find($warga_id)->delete();
			Wu::find($warga_id)->delete();
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the User";
			return response()->json($response);
			exit();
		}


			// success
			$response['status'] = "success";
			$response['message'] = "Email $email delete";
			return response()->json($response);
			exit();

	}

	//list crm
	public function list_crm($wil_id, Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $length = $request->get("length");
        $search = $request->get('search')['value'];

        $order =  $request->get('order');

         $col = 0;
         $dir = "";
         if(!empty($order)) {
             foreach($order as $o) {
                 $col = $o['column'];
                 $dir= $o['dir'];
             }
        }

         if($dir != "asc" && $dir != "desc") {
             $dir = "asc";
         }
         $columns_valid = array("warga_id", "warga_nama_depan", "warga_alamat", "warga_hp");
         if(!isset($columns_valid[$col])) {
            $order = 'null';
        } else {
            $order = $columns_valid[$col];
        }
        //$info = $wilayah->get_list($start, $length, $order, $dir, $search);
        $res = DB::table('warga as a')
					->join('wilayah as b','a.wil_id','=','b.wil_id')
					->join('kelurahan as kel', 'kel.kel_id','=','b.kel_id')
					->join('kecamatan as kec', 'kec.kec_id','=','kel.kec_id')
					->join('kabkota as kab', 'kab.kabkota_id','=','kec.kabkota_id')
					->join('propinsi as pro', 'pro.prop_id','=','kab.prop_id')
					->select('b.wil_nama', 'b.wil_alamat', 'b.wil_status', 'a.warga_id', 'a.warga_nama_depan', 'a.warga_nama_belakang', 'a.warga_alamat', 'a.warga_hp', 'a.warga_status', 'a.warga_status_rumah', 'a.warga_tgl_lahir', 'a.warga_email', 'kel.kel_nama', 'kec.kec_nama', 'kab.kabkota_nama', 'pro.prop_nama')
					->where('a.wil_id',$wil_id);
        if($search!=''){
            $res = $res->where('a.warga_nama_depan','ilike',"%$search%")
						->orwhere('a.warga_nama_belakang','ilike',"%$search%");
        }
		if(isset($order)){
			$res = $res->orderBy($order, $dir);
		}else{
			$order = $res->orderBy('a.wil_id');
		}
		if(isset($length) || isset($start)){
			$res = $res->skip($start)->take($length);
		}
        $res = $res->get();
        $i = 1;
        $data = array();
		if(!empty($res) || $res !=''){

			foreach($res as $r) {
				$rumah = array('Rumah Sendiri', 'Rumah Keluarga', 'Kontrak');
				if(!preg_match("/^[-]+$/", $r->warga_status_rumah) && isset($r->warga_status_rumah) && $r->warga_status_rumah !='' && !empty($r->warga_status_rumah)){
					$s_rumah = $rumah[intval($r->warga_status_rumah)-1];
				}else{
					$s_rumah = '-';
				}
				if(!$r->warga_tgl_lahir){
					$tgl_lahir='-';
				}else{
					$tgl_lahir = (Carbon::parse($r->warga_tgl_lahir)->format('d-m-Y') );
				}
				$data[] = array(
					$start + $i,
					$r->warga_nama_depan.' '.$r->warga_nama_belakang,
					$r->warga_alamat,
					$r->warga_hp,
					'Warga Tetap',
					$s_rumah,
					$tgl_lahir,
					$r->warga_email,
					'<form action="warga/'.$r->warga_id.'/destroy" method="POST"> <a href="#edit-warga" onclick="showWar('.$r->warga_id.')" id="edit-warga-btn" data-idw="'.$r->warga_id.'"><i class="fa fa-edit fa-lg text-success" title="Edit"></i></a> <a href="#" id="hapus" data-id="'.$r->warga_id.'" data-nama="'.$r->warga_nama_depan.'"><i class="fa fa-trash fa-lg text-danger" title="Hapus"></a></form>'
				);
				$i++;
			}
			//total data lead
		   $total_sal = DB::table('warga as a')
		   					->join('wilayah as b','a.wil_id','=','b.wil_id')
							->where('a.wil_id',$wil_id)->count();
		   //total filtered
		   $total_fil = DB::table('warga as a')
		   					->join('wilayah as b','a.wil_id','=','b.wil_id')
							->where('a.wil_id',$wil_id);
		   if($search!=''){
				$total_fil = $res->where('a.wil_nama','ilike',"%$search%");
			}
			$total_fil = $total_fil->count();

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
	//dashboard penggunan
	public function dash_user()
	{
		$total_user = Warga::count();

		$total_user_month = DB::table('warga as a', '')
			->join('core_user as b','a.warga_id','=','b.user_ref_id')
			->whereRaw('EXTRACT(month FROM b.created_at) = EXTRACT(month FROM NOW())')
			->get()->count();

		$total_user_pmonth = DB::table('warga as a', '')
			->join('core_user as b','a.warga_id','=','b.user_ref_id')
			->whereRaw('EXTRACT(month FROM b.created_at) < EXTRACT(month FROM NOW())')
			->get()->count();

		$total_user_day = DB::table('warga as a')
			->join('core_user as b','a.warga_id','=','b.user_ref_id')
			->whereRaw('EXTRACT(day FROM b.created_at) = EXTRACT(day FROM NOW())')
			->whereRaw('EXTRACT(month FROM b.created_at) = EXTRACT(month FROM NOW())')
			->get()->count();

		$total_user_pday = DB::table('warga as a')
			->join('core_user as b','a.warga_id','=','b.user_ref_id')
			->whereRaw('b.created_at < NOW()')
			->get()->count();

		$res = array(
			"total_user" => $total_user,
			"user_month" => $total_user_month,
			"user_pmonth" => $total_user_pmonth,
			"user_day" => $total_user_day,
			"user_pday" => $total_user_pday
		);
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['res'] = $res;

		// return json response
		return response()->json($response);

	}
	//dapat nama depan & belakang
	function getFirstName($name) {
		return implode(' ', array_slice(explode(' ', $name), 0, -1));
	}

	function getLastName($name) {
		return array_slice(explode(' ', $name), -1)[0];
	}
	//----/

	/*== Check Email Warga ==*/
	public function check_email(Request $request, User $user)
	{
		$email = $request->email;

		$warga_email = $user->get_email($email);
		if(!empty($warga_email))
		{
			$response['status'] = "error";
			$response['message'] = "Email sudah terdaftar, silakan gunakan Email yang lain.";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "Email belum terdaftar";
		// return json response
		return response()->json($response);
	}

	/*== Check HP Warga ==*/
	public function check_hp(Request $request, Warga $warga)
	{
		$hp = $request->hp;

		$warga_hp = $warga->get_hp($hp);
		if(!empty($warga_hp))
		{
			$response['status'] = "error";
			$response['message'] = "No. HP sudah terdaftar, silakan gunakan No. HP yang lain.";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "No. HP belum terdaftar";
		// return json response
		return response()->json($response);
	}

}