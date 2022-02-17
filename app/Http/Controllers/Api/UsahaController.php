<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\JadwalBuka;
use App\JenisUsaha;
use App\Usaha;
use App\Wilayah;
use Illuminate\Http\Request;
use Response;
use Intervention\Image\ImageManagerStatic as Image;
use File;


class UsahaController extends Controller
{

    public function list(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $keyword = $request->keyword;

        $usaha = new Usaha;
        $list = $usaha->getList($wil_id, $keyword);

        $i = 0;
        $data = array();
        foreach($list as $item) {
            $data[$i] = json_decode(json_encode($item), true);

            $statusHariIni = $usaha->statusHariIni($item->usaha_id);
            $data[$i]['status_buka_tutup'] = $statusHariIni['status'];
            $data[$i]['jam_buka'] = $statusHariIni['jam_buka'];

            $data[$i]['foto_url'] =  URL('public/img/ecommerce/usaha/'.$item->usaha_foto);
            $i++;
        }

        // response
        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $data;

		// return json response
		return response()->json($response);
    }


    public function list_limited(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $keyword = $request->keyword;

        $page = empty($request->page) ? 1 : $request->page;
        $limit = empty($request->limit) ? 20 : $request->limit;

        $usaha = new Usaha;
        $list = $usaha->getListLimited($wil_id, $keyword, $page, $limit);

        $i = 0;
        $data = array();
        foreach($list as $item) {
            $data[$i] = json_decode(json_encode($item), true);

            $statusHariIni = $usaha->statusHariIni($item->usaha_id);
            $data[$i]['status_buka_tutup'] = $statusHariIni['status'];
            $data[$i]['jam_buka'] = $statusHariIni['jam_buka'];

            $data[$i]['foto_url'] =  URL('public/img/ecommerce/usaha/'.$item->usaha_foto);
            $i++;
        }

        // response
        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $data;

		// return json response
		return response()->json($response);
    }

    public function add(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $warga_id = $request->warga_id;
        $ju_id = $request->ju_id;
        $usaha_nama = $request->usaha_nama;
        $usaha_wa = $request->usaha_wa;
        $usaha_foto = $request->file('usaha_foto');
        $usaha_lokasi = $request->usaha_lokasi;
        $usaha_geo = $request->usaha_geo;

        $usaha = new Usaha;

        if(!empty($usaha_foto)) {
            $destination_path = public_path('img/ecommerce/usaha/');
			$img = $usaha_foto;

			// upload
			$md5_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

			// resize photo
			$img = Image::make(URL("public/img/ecommerce/usaha/$md5_name.$ext"));
			$img->fit(1024,768);
			$img->save(public_path("img/ecommerce/usaha/$md5_name.$ext"));

			// set data
			$usaha->usaha_foto = $img_file;
        }else {
            $usaha->usaha_foto = "default.jpg";
        }

        $usaha->wil_id = $wil_id;
        $usaha->warga_id = $warga_id;
        $usaha->ju_id = $ju_id;
        $usaha->usaha_nama = substr($usaha_nama,0,150);
        $usaha->usaha_wa = substr($usaha_wa,0,14);
        $usaha->usaha_sts = '1';
        $usaha->usaha_lokasi = substr($usaha_lokasi,0,150);
        $usaha->usaha_geo = substr($usaha_geo,0,100);
        $usaha->save();

        //create jadwal
        JadwalBuka::create([
            'usaha_id'      => $usaha->usaha_id,
            'jb_ming_libur' => '1',
            'jb_ming_buka'  => '07:00',
            'jb_ming_tutup' => '18:00',
            'jb_sen_libur'  => '0',
            'jb_sen_buka'   => '07:00',
            'jb_sen_tutup'  => '18:00',
            'jb_sel_libur'  => '0',
            'jb_sel_buka'   => '07:00',
            'jb_sel_tutup'  => '18:00',
            'jb_rab_libur'  => '0',
            'jb_rab_buka'   => '07:00',
            'jb_rab_tutup'  => '18:00',
            'jb_kam_libur'  => '0',
            'jb_kam_buka'   => '07:00',
            'jb_kam_tutup'  => '18:00',
            'jb_jum_libur'  => '0',
            'jb_jum_buka'   => '07:00',
            'jb_jum_tutup'  => '18:00',
            'jb_sab_libur'  => '0',
            'jb_sab_buka'   => '07:00',
            'jb_sab_tutup'  => '18:00',
        ]);

        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $usaha;

		// return json response
		return response()->json($response);
    }


    public function edit(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $usaha_id = $request->usaha_id;
        $ju_id = $request->ju_id;
        $usaha_nama = $request->usaha_nama;
        $usaha_wa = $request->usaha_wa;
        $usaha_foto = $request->file('usaha_foto');
        $usaha_lokasi = $request->usaha_lokasi;
        $usaha_geo = $request->usaha_geo;

        $usaha = Usaha::find($usaha_id);

        $wil_id = $usaha->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        if(!empty($usaha_foto)) {

            $destination_path = public_path('img/ecommerce/usaha/');
			$img = $usaha_foto;

			// upload
			$md5_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

			// resize photo
			$img = Image::make(URL("public/img/ecommerce/usaha/$md5_name.$ext"));
			$img->fit(1024,768);
			$img->save(public_path("img/ecommerce/usaha/$md5_name.$ext"));


            if($usaha->usaha_foto != 'default.jpg')
                File::delete(public_path('img/ecommerce/usaha/').$usaha->usaha_foto);
            // set data
			$usaha->usaha_foto = $img_file;
        }

        $usaha->usaha_id = $usaha_id;
        $usaha->ju_id = $ju_id;
        $usaha->usaha_nama = substr($usaha_nama,0,150);
        $usaha->usaha_wa = substr($usaha_wa,0,14);
        $usaha->usaha_lokasi = substr($usaha_lokasi,0,150);
        $usaha->usaha_geo = substr($usaha_geo,0,100);
        $usaha->save();


        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $usaha;

		// return json response
		return response()->json($response);
    }

    public function detail(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $usaha_id = $request->usaha_id;
        $usaha = Usaha::find($usaha_id);

        $jenisUsaha = JenisUsaha::find($usaha->ju_id);
        $jadwalBuka = JadwalBuka::where('usaha_id',$usaha_id)->first();
        $usaha->ju_nama = $jenisUsaha->ju_nama;

        $statusHariIni = $usaha->statusHariIni($usaha_id);
        $usaha->status_buka_tutup = $statusHariIni['status'];
        $usaha->jam_buka = $statusHariIni['jam_buka'];
        $usaha->status_libur = $statusHariIni['status_libur'];
        $usaha->foto_url =  URL('public/img/ecommerce/usaha/'.$usaha->usaha_foto);

        $response['status'] = "success";
		$response['message'] = "OK";
        $response['usaha'] = $usaha;
        $response['jadwal_buka'] = $jadwalBuka;
        $response['jenis_usaha'] = JenisUsaha::all();
        $response['status_hari_ini'] = $statusHariIni;

        // return json response
		return response()->json($response);
    }

    public function profil(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $usaha_id = $request->usaha_id;
        $usaha = Usaha::find($usaha_id);
        $jenisUsaha = JenisUsaha::find($usaha->ju_id);
        $usaha->ju_nama = $jenisUsaha->ju_nama;

        $statusHariIni = $usaha->statusHariIni($usaha_id);
        $usaha->status_buka_tutup = $statusHariIni['status'];
        $usaha->jam_buka = $statusHariIni['jam_buka'];
        $usaha->foto_url =  URL('public/img/ecommerce/usaha/'.$usaha->usaha_foto);

        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $usaha;

        // return json response
		return response()->json($response);
    }



}
