<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use DB;
use App\Provinsi;
use App\Kab;
use App\Kec;
use App\Kel;

use Illuminate\Support\Str;

class CountryController extends Controller
{
	private $ctrl = 'provinsi';
	private $title = 'Provinsi';

	/*==  List Data Provinsi ==*/
	public function prov_list(Request $request, Provinsi $provinsi) 
	{

		//get data
		$provinsi = $provinsi->get_list();
		if(empty($provinsi))
		{
			$response['status'] = "error";
			$response['message'] = "Provinsi not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $provinsi;
		
		// return json response
		return response()->json($response);

		// Test database connection
		// try {
		//     DB::connection()->getPdo();
		// } catch (\Exception $e) {
		//     die("Could not connect to the database.  Please check your configuration. error:" . $e );
		// }
		
	}

	/*==  List Data Provinsi by keyWords ==*/
	public function provinsi_list(Request $request, Provinsi $provinsi) 
	{	

		$keyword = $request->keyword;

		//get data
		$provinsi = $provinsi->get_list($keyword);
		if(empty($provinsi))
		{
			$response['status'] = "error";
			$response['message'] = "Provinsi not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $provinsi;
		
		// return json response
		return response()->json($response);

		// Test database connection
		// try {
		//     DB::connection()->getPdo();
		// } catch (\Exception $e) {
		//     die("Could not connect to the database.  Please check your configuration. error:" . $e );
		// }
		
	}

	/*==  List Data Kab ==*/
	public function kab_list($prop_id, Request $request, Kab $kab) 
	{

		// validate param
		if($prop_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "Provinsi is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$kab = $kab->get_list($prop_id);
		if(empty($kab))
		{
			$response['status'] = "error";
			$response['message'] = "Kab/Kot not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kab;
		
		// return json response
		return response()->json($response);
		
	}

	/*==  List Data Kab ==*/
	public function kabupaten_list(Request $request, Kab $kab) 
	{

		$keyword = $request->keyword;
		$prop_id = $request->prop_id;

		// validate param
		if($prop_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "Provinsi is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$kab = $kab->get_list($prop_id, $keyword);
		if(empty($kab))
		{
			$response['status'] = "error";
			$response['message'] = "Kab/Kot not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kab;
		
		// return json response
		return response()->json($response);
		
	}

	/*==  List Data Kec ==*/
	public function kec_list($kabkot_id, Request $request, Kec $kec) 
	{

		// validate param
		if($kabkot_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "Kab/Kota is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$kec = $kec->get_list($kabkot_id);
		if(empty($kec))
		{
			$response['status'] = "error";
			$response['message'] = "Kec not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kec;
		
		// return json response
		return response()->json($response);
		
	}

	/*==  List Data Kec ==*/
	public function kecamatan_list(Request $request, Kec $kec) 
	{

		$keyword = $request->keyword;
		$kabkot_id = $request->kabkot_id;

		// validate param
		if($kabkot_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "Kab/Kota is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$kec = $kec->get_list($kabkot_id, $keyword);
		if(empty($kec))
		{
			$response['status'] = "error";
			$response['message'] = "Kec not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kec;
		
		// return json response
		return response()->json($response);
		
	}

	/*==  List Data Kel ==*/
	public function kel_list($kec_id, Request $request, Kel $kel) 
	{

		// validate param
		if($kec_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "Kec is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$kel = $kel->get_list($kec_id);
		if(empty($kel))
		{
			$response['status'] = "error";
			$response['message'] = "Kel not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kel;
		
		// return json response
		return response()->json($response);
		
	}
	/*==  List Data Kecamatan based on Kelurahan ==*/
	public function kec_kel($kel_id, Request $request, Kec $kec) 
	{

		// validate param
		if($kel_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "Kel is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$kec = $kec->get_kec_list($kel_id);
		if(empty($kec))
		{
			$response['status'] = "error";
			$response['message'] = "Kec not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kec;
		
		// return json response
		return response()->json($response);
		
	}

	/*==  List Data Kabkota based on Kecl==*/
	public function kab_kec($kel_id, Request $request, Kab $kab) 
	{

		// validate param
		if($kel_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "Kel is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$kab = $kab->get_kab($kel_id);
		if(empty($kab))
		{
			$response['status'] = "error";
			$response['message'] = "Kab not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kab;
		
		// return json response
		return response()->json($response);
		
	}
	/*==  List Data Kabkota based on Kec ==*/
	public function prov_kab($kab_id, Request $request, Provinsi $pro) 
	{

		// validate param
		if($kab_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "Kab is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$prop = $pro->get_prov($kab_id);
		if(empty($prop))
		{
			$response['status'] = "error";
			$response['message'] = "Prop not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $prop;
		
		// return json response
		return response()->json($response);
		
	}


	/*==  List Data Kel ==*/
	public function kelurahan_list(Request $request, Kel $kel) 
	{

		$keyword = $request->keyword;
		$kec_id = $request->kec_id;

		// validate param
		if($kec_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "Kec is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$kel = $kel->get_list($kec_id, $keyword);
		if(empty($kel))
		{
			$response['status'] = "error";
			$response['message'] = "Kel not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kel;
		
		// return json response
		return response()->json($response);
		
	}

	/*==  List Data Kel Nama ==*/
	public function kel_nama($kel_id, Request $request, Kel $kel) 
	{

		// validate param
		if($kel_id=='')
		{
			$response['status'] = "error";
			$response['message'] = "kel_id is required fields";
			return response()->json($response);
			exit();
		}

		// get data
		$kel = $kel->get_kel_nama($kel_id);
		if(empty($kel))
		{
			$response['status'] = "error";
			$response['message'] = "Kel not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $kel;
		
		// return json response
		return response()->json($response);
		
	}


	/*==  Convert Uppercase ==*/
	public function prov(Request $request, Provinsi $provinsi) 
	{

		//get data
		$provinsi = $provinsi->get_list();
		
		foreach ($provinsi as $row) {

			$prop_id = $row->prop_id;
			$prop_nama = Str::lower($row->prop_nama);
			$prop_nama_ = Str::of($prop_nama)->title();

			$provinsi = Provinsi::find($prop_id);
			$provinsi->prop_nama = $prop_nama_;
			$provinsi->save();
		}

		
	}

	/*==  Convert Uppercase ==*/
	public function kab(Request $request, Kab $kab) 
	{

		//get data
		$kab = $kab->get_list_();
		
		foreach ($kab as $row) {

			$kabkota_id = $row->kabkota_id;
			$kabkota_nama = Str::lower($row->kabkota_nama);
			$kabkota_nama_ = Str::of($kabkota_nama)->title();

			$kab = Kab::find($kabkota_id);
			$kab->kabkota_nama = $kabkota_nama_;
			$kab->save();
		}

		
	}

	/*==  Convert Uppercase ==*/
	public function kec(Request $request, Kec $kec) 
	{

		//get data
		$kec = $kec->get_list_();
		
		foreach ($kec as $row) {

			$kec_id = $row->kec_id;
			$kec_nama = Str::lower($row->kec_nama);
			$kec_nama_ = Str::of($kec_nama)->title();

			$kec = Kec::find($kec_id);
			$kec->kec_nama = $kec_nama_;
			$kec->save();
		}

		
	}

	/*==  Convert Uppercase ==*/
	public function kel(Request $request, Kel $kel) 
	{

		//get data
		$kel = $kel->get_list_();
		
		foreach ($kel as $row) {

			$kel_id = $row->kel_id;
			$kel_nama = Str::lower($row->kel_nama);
			$kel_nama_ = Str::of($kel_nama)->title();

			//print_r($kel_nama_);

			$kel = Kel::find($kel_id);
			$kel->kel_nama = $kel_nama_;
			$kel->save();
		}

		
	}

}