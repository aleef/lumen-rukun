<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Response;
use App\Peraturan;
use App\Wilayah;
use Carbon\Carbon;
use File;


class PeraturanController extends Controller
{

	/*==  List ==*/
	public function list(Request $request)
	{

		$wil_id = $request->wil_id;
		$keyword = $request->keyword;
		$peraturan_kat = $request->peraturan_kat;

		$peraturan = Peraturan::where('wil_id',$wil_id);

        if($peraturan_kat != '')
            $peraturan = $peraturan->where('peraturan_kat',$peraturan_kat);

        if($keyword != '') {
            $peraturan = $peraturan->where(function($q) use ($keyword) {
                                $q->where('peraturan_judul','ilike',"%$keyword%")
                                ->orWhere('peraturan_isi','ilike',"%$keyword%");
                        });
        }

        $listPeraturan = $peraturan->get();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $listPeraturan;

		// return json response
		return response()->json($response);
	}

    /*==  List ==*/
	public function list_limited(Request $request)
	{

		$wil_id = $request->wil_id;
		$keyword = $request->keyword;
		$peraturan_kat = $request->peraturan_kat;

        $page = empty($request->page) ? 1 : $request->page;
        $limit = empty($request->limit) ? 20 : $request->limit;

		$peraturan = Peraturan::where('wil_id',$wil_id);

        if($peraturan_kat != '')
            $peraturan = $peraturan->where('peraturan_kat',$peraturan_kat);

        if($keyword != '') {
            $peraturan = $peraturan->where(function($q) use ($keyword) {
                                $q->where('peraturan_judul','ilike',"%$keyword%")
                                ->orWhere('peraturan_isi','ilike',"%$keyword%");
                        });
        }

        $listPeraturan = $peraturan->orderBy('peraturan_id','desc')
        ->limit($limit)->offset(($page-1)*$limit)
        ->get();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $listPeraturan;

		// return json response
		return response()->json($response);
	}

	/*==  Detail ==*/
	public function detail(Request $request)
	{
		// get data
		$peraturan = Peraturan::find($request->peraturan_id);
		if(empty($peraturan))
		{
			$response['status'] = "error";
			$response['message'] = "Peraturan not found";
			return response()->json($response);
			exit();
		}

        if($peraturan->peraturan_file != '')
            $peraturan->base_file_name = basename($peraturan->peraturan_file);
		else
            $peraturan->base_file_name = '';
        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $peraturan;

		// return json response
		return response()->json($response);
	}

	/*== Add ==*/
	public function add(Request $request)
	{
		// data
		$wil_id = $request->wil_id;

        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

		$peraturan_judul = $request->peraturan_judul;
		$peraturan_isi = $request->peraturan_isi;
		$peraturan_kat = $request->peraturan_kat;

		$peraturan = new Peraturan;

		// upload img
		if($request->file('peraturan_file')!='')
		{
			// destination path
			$destination_path = public_path('peraturan/');
			$img = $request->file('peraturan_file');

			$file_name = substr(str_replace(' ','_',$peraturan_judul),0,50);
			$md5_name = strtolower($file_name."_".$peraturan_kat.Carbon::now()->timestamp);
			// upload
			//$md5_name = $peraturan_kat."_".uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

			// set data
			$peraturan->peraturan_file = $img_file;

		}else{
			// set data
			$peraturan->peraturan_file = '';
		}

		if($request->file('peraturan_foto')!='')
		{
			// destination path
			$destination_path = public_path('peraturan/');
			$img = $request->file('peraturan_foto');

			// upload
			$md5_name = $peraturan_kat."_".uniqid()."_".md5_file($img->getRealPath());
            $ext = $img->getClientOriginalExtension();


			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

			// resize photo
			$img = Image::make(public_path("peraturan/$md5_name.$ext"));
			$img->fit(1024,768);
			$img->save(public_path("peraturan/$md5_name.$ext"));

			// set data
			$peraturan->peraturan_foto = $img_file;

		}else{
			// set data
			$peraturan->peraturan_foto = 'default.jpg';
		}

		//set data peraturan
		$peraturan->wil_id = $wil_id;
		$peraturan->peraturan_kat = $peraturan_kat;
		$peraturan->peraturan_judul = $peraturan_judul;
		$peraturan->peraturan_isi = $peraturan_isi;
		$peraturan->create_date = Carbon::now();
		$peraturan->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}

	/*== Update ==*/
	public function update(Request $request)
	{
		// data
		$peraturan_id = $request->peraturan_id;
        $peraturan_kat = $request->peraturan_kat;
		$peraturan_judul = $request->peraturan_judul;
		$peraturan_isi = $request->peraturan_isi;

		$peraturan = Peraturan::find($peraturan_id);

        $wil_id = $peraturan->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        // upload img
		if($request->file('peraturan_file')!='')
		{
			// destination path
			$destination_path = public_path('peraturan/');
			$img = $request->file('peraturan_file');

			// upload

            $file_name = substr(str_replace(' ','_',$peraturan_judul),0,50);
			$md5_name = strtolower($file_name."_".$peraturan_kat.Carbon::now()->timestamp);

			//$md5_name = $peraturan_kat."_".uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

            if($peraturan->peraturan_file != '')
                File::delete(public_path('peraturan/').$peraturan->peraturan_file);

			// set data
			$peraturan->peraturan_file = $img_file;

		}

		if($request->file('peraturan_foto')!='')
		{
			// destination path
			$destination_path = public_path('peraturan/');
			$img = $request->file('peraturan_foto');

			// upload
			$md5_name = $peraturan_kat."_".uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

			// resize photo
			$img = Image::make(public_path("peraturan/$md5_name.$ext"));
			$img->fit(1024,768);
			$img->save(public_path("peraturan/$md5_name.$ext"));

            //remove picture first
            if($peraturan->peraturan_foto != 'default.jpg')
                File::delete(public_path('peraturan/').$peraturan->peraturan_foto);

			// set data
			$peraturan->peraturan_foto = $img_file;

		}

        //set data pengurus
		$peraturan->peraturan_id = $peraturan_id;
		$peraturan->peraturan_judul = $peraturan_judul;
		$peraturan->peraturan_isi = $peraturan_isi;
		$peraturan->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}


    public function delete(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $peraturan_id = $request->peraturan_id;
        $peraturan = Peraturan::find($peraturan_id);

        $wil_id = $peraturan->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }


        if($peraturan->peraturan_foto != 'default.jpg')
            File::delete(public_path('peraturan/').$peraturan->peraturan_foto);
        if($peraturan->peraturan_file != '')
            File::delete(public_path('peraturan/').$peraturan->peraturan_file);


        $peraturan->delete();

        $response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
    }
}
