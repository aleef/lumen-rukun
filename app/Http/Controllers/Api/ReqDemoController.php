<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Carbon\Carbon;
use File;
use Bitly;
use Response;
use Mail;
use App\ReqDemo;
use App\Wilayah;
use App\GlobalVariable;

class ReqDemoController extends Controller
{
	private $ctrl = 'rd';
	private $title = 'Request';

	/*==  List Data ==*/
	public function list(Request $request, Warga $rd)
	{
		$rd_id = $request->wil_id;
		$rd_id = $request->rd_id;
		$keyword = $request->keyword;
		// validate param
		if($rd_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "wil_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$rd = $rd->get_list($rd_id, $keyword, $rd_id);
		if(empty($rd))
		{
			$response['status'] = "error";
			$response['message'] = "Warga not found";
			return response()->json($response);
			exit();
		}

		$i=0;
        foreach($rd as $row)
        {
            if($row->rd_foto!='')
            {
                $rd_foto = URL('public/img/pp/'.$row->rd_foto);
            }else{
                $rd_foto = URL('public/img/pp/default.png');
            }

            $result[$i]['rd_id'] = $row->rd_id;
            $result[$i]['rd_nama_depan'] = $row->rd_nama_depan;
            $result[$i]['rd_nama_belakang'] = $row->rd_nama_belakang;
            $result[$i]['rd_email'] = $row->rd_email;
            $result[$i]['rd_alamat'] = $row->rd_alamat;
            $result[$i]['rd_no_rumah'] = $row->rd_no_rumah;
            $result[$i]['rd_geo'] = $row->rd_geo;
            $result[$i]['rd_foto'] = $rd_foto;
            $result[$i]['rd_hp'] = $row->rd_hp;
            $result[$i]['rd_tgl_lahir'] = Carbon::parse($row->rd_tgl_lahir)->format('d M Y');
            $result[$i]['wil_id'] = $row->wil_id;
            $result[$i]['rd_status'] = $row->rd_status;
            $i++;
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $result;

		// return json response
		return response()->json($response);

	}

	public function detail($rd_id, Request $request, Warga $rd)
	{
		// validate param
		if($rd_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "rd_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$rd = $rd->get_detail($rd_id);
		// $rd = Info::find($rd_id);
		if(empty($rd))
		{
			$response['status'] = "error";
			$response['message'] = "Warga not found";
			return response()->json($response);
			exit();
		}

		//
		if($rd->rd_foto!='')
		{
			$rd_foto = URL('public/img/pp/'.$rd->rd_foto);
		} else {
			$rd_foto = URL('public/img/pp/default.png');
		}

		switch ($rd->rd_status_rumah) {
			case '':
				$rd_status_rumah = '-';
				break;

            case '-':
                $rd_status_rumah = '-';
                break;

            case '0':
                $rd_status_rumah = '-';
                break;

			case '1':
				$rd_status_rumah = 'Rumah Sendiri';
				break;

			case '2':
				$rd_status_rumah = 'Rumah Keluarga';
				break;

			default:
				$rd_status_rumah = 'Kontrak/Sewa';
				break;
		}

		switch ($rd->rd_status) {
			case '':
				$rd_status = '-';
				break;

            case '-':
                $rd_status = '-';
                break;

            case '0':
                $rd_status = '-';
                break;

			case '1':
				$rd_status = 'Penduduk Tetap';
				break;

			default:
				$rd_status = 'Penduduk Non Permanen';
				break;
		}

		$results = array(
			"rd_id" => $rd->rd_id,
			"rd_nama_depan" => $rd->rd_nama_depan,
			"rd_nama_belakang" => $rd->rd_nama_belakang,
			"rd_email" => $rd->rd_email,
			"rd_alamat" => $rd->rd_alamat,
			"rd_no_rumah" => $rd->rd_no_rumah,
			"rd_geo" => $rd->rd_geo,
			"rd_foto" => $rd_foto,
			"rd_hp" => $rd->rd_hp,
			"rd_tgl_lahir" => (!empty($rd->rd_tgl_lahir)) ? Carbon::parse($rd->rd_tgl_lahir)->isoFormat('D MMMM Y') : "",
            "tgl_lahir" => $rd->rd_tgl_lahir,
			"wil_id" => $rd->wil_id,
			"rd_status" => $rd_status,
			"rd_status_rumah" => $rd_status_rumah,
            "rd_status_id" => $rd->rd_status,
            "rd_status_rumah_id" => $rd->rd_status_rumah,
			"kb_id" => $rd->kb_id,
			"wil_nama" => $rd->wil_nama,
			"kel_id" => $rd->kel_id,
			"wil_alamat" => $rd->wil_alamat,
			"wil_geolocation" => $rd->wil_geolocation,
			"wil_foto" => $rd->wil_foto,
			"wil_jenis" => $rd->wil_jenis,
			"fcm_token" => $rd->fcm_token,
			"kb_keterangan" => $rd->kb_keterangan,
			"kb_tarif_ipl" => $rd->kb_tarif_ipl,
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
		// get param

		$rd = new ReqDemo();

		$rd->rd_nama =  $request->rd_nama;
		$rd->rd_hp = $request->rd_hp;
		$rd->rd_email = $request->rd_email;
		$rd->rd_jenis_wilayah = $request->rd_jenis_wilayah;
		$rd->rd_jml_warga = $request->rd_jml_warga;
		$rd->kabkota_id = $request->kabkota_id;
		$rd->save();
			
		
		//$res = 
		/*DB::insert(DB::raw("insert into request_demo(rd_nama, rd_hp, rd_email, rd_jenis_wilayah, rd_jml_warga, kabkota_id) 
		values ('".$request->rd_nama."', '".$request->rd_hp."', '".$request->rd_email."','".$request->rd_jenis_wilayah."', '".$request->rd_jml_warga."', ".$request->kabkota_id.");"));*/

		$results = array(
			"nama" => $request->rd_nama,
			"email" => $request->rd_email,
			"phone" => $request->rd_hp
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);

	}
	

}
