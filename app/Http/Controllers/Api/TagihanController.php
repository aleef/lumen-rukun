<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Response;
use App\Jt;
use App\Jti;
use App\Tagihan;
use App\Warga;
use App\Periodetagihan;
use App\Dt;
use App\Kb;
use App\Wilayah;

class TagihanController extends Controller
{
	private $ctrl = 'tagihan';
	private $title = 'Tagihan';


	/*== Add Tagihan ==*/
	public function add_tagihan(Request $request, Kb $kb)
	{
		$today = date('Y-m-d');
		$today_m = date('m');
		$today_y = date('Y');
		$bulan = $request->bulan;
		$tahun = $request->tahun;

		//validasi bulan dan tahun
		if(($bulan == $today_m) && ($tahun == $today_y)){

			//now periode ipl
			$pt = Periodetagihan::where([
				['pt_tahun',$tahun],
				['pt_bulan',$bulan]
			])->first();

			$pt_id = $pt->pt_id;
			//print($pt);

			// data
			$wil_id = $request->wil_id;

			//find warga id
			$warga = Warga::where('wil_id',$wil_id)->get();

			foreach ($warga as $rows) {
				$warga_id = $rows->warga_id;
				$cb_id = 1;
				$pt_id = $pt_id;
				$tag_total = 0;
				$tag_status = '0';
				$tag_tgl_bayar = null;
				$wil_id = $wil_id;
				//
				$tagihan = new Tagihan;

				//set data
				$tagihan->warga_id = $warga_id;
				$tagihan->cb_id = $cb_id;
				$tagihan->pt_id = $pt_id;
				$tagihan->tag_total = $tag_total;
				$tagihan->tag_status = $tag_status;
				$tagihan->tag_tgl_bayar = $tag_tgl_bayar;
				$tagihan->wil_id = $wil_id;
				$tagihan->tag_create = date('Y-m-d');
				$tagihan->save();

			}

			//get tag_id
			$tag = Tagihan::where(		[
				['wil_id',$wil_id],
				['tag_create',$today]
			])->get();

			foreach ($tag as $rows) {
				$tag_id = $rows->tag_id;
				$warga_id = $rows->warga_id;

				//get jenis tagihan
				$jt = Jt::where('wil_id',$wil_id)->get();

				foreach ($jt as $rows) {

					$jt_id = $rows->jt_id;

					$dt = new Dt;

					$dt->jt_id = $jt_id;
					$dt->dt_qty = 0;
					$dt->tag_id = $tag_id;
					$dt->save();
				}

				//get jenis tagihan insidental
				$jti = Jti::where('wil_id',$wil_id)->get();
				if(!empty($jti)){

					foreach ($jti as $rows) {

						$jti_id = $rows->jti_id;

						$dt = new Dt;

						$dt->jti_id = $jti_id;
						$dt->dt_qty = 0;
						$dt->tag_id = $tag_id;
						$dt->save();
					}

				}

				//ipl kategori bangunan berdasarkan warga_id
				$results_kb = $kb->get_kb_nominal($warga_id);

				//print_r($results_kb);

				$kb_id = $results_kb->kb_id;
				$nominal = $results_kb->kb_tarif_ipl;

				//print($kb_id);

				$dt = new Dt;

				$dt->kb_id = $kb_id;
				$dt->dt_qty = 1;
				$dt->tag_id = $tag_id;
				$dt->dt_nominal = $nominal;
				$dt->save();

			}

			$results = array(
				"bulan" => $bulan,
				"tahun" => $tahun
			);

			// response
			$response['status'] = "success";
			$response['message'] = "Success";
			$response['results'] = $results;

			// return json response
			return response()->json($response);

		}elseif($bulan != $today_m){
			// response
			$response['status'] = "failed";
			$response['message'] = "Bulan tidak sesuai";

			// return json response
			return response()->json($response);
		}elseif($tahun != $today_y){
			// response
			$response['status'] = "failed";
			$response['message'] = "Tahun tidak sesuai";

			// return json response
			return response()->json($response);
		}


	}

	/*== Update Tagihan ==*/
	public function update_tagihan(Request $request)
	{
		$tag_id = $request->tag_id;
		$tag_status = $request->tag_status;
		$tag_tgl_bayar = $request->tag_tgl_bayar;
		$cb_id = $request->cb_id;
		$tag_total = $request->tag_total;
		$tag_status_warga = $request->tag_status_warga;

		$tagihan = Tagihan::find($tag_id);
		$tagihan->tag_id = $tag_id;
		$tagihan->tag_status = $tag_status;
		$tagihan->tag_tgl_bayar = $tag_tgl_bayar;
		$tagihan->cb_id = $cb_id;
		$tagihan->tag_total = $tag_total;
		$tagihan->tag_status_warga = $tag_status_warga;
		$tagihan->save();

		$results = array(
			"tag_status" => $tag_status
		);

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $results;

		// return json response
		return response()->json($response);

	}

	/*== Update Tagihan Per Periode ==*/
	public function update_tagihan_periode(Request $request, Tagihan $tagihan)
	{
		$wil_id = $request->wil_id;
		$tag_bulan = $request->tag_bulan;
		$tag_tahun = $request->tag_tahun;
		$tag_status_warga = $request->tag_status_warga;

		$results = $tagihan->get_list_periode($wil_id, $tag_bulan, $tag_tahun);

		foreach ($results as $row) {
			$tag_id = $row->tag_id;
			//
			$tagihan = Tagihan::find($tag_id);
			$tagihan->tag_status_warga = $tag_status_warga;
			$tagihan->save();


		}

		$results = array(
				"tag_status_warga" => $tag_status_warga
		);

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $results;

		// return json response
		return response()->json($response);

	}

	/*==  List ==*/
	public function list(Request $request, Tagihan $tagihan)
	{
		$wil_id = $request->wil_id;
		$keyword = $request->keyword;
		$bulan = $request->bulan;
		$tahun = $request->tahun;
		$tag_status = $request->tag_status;

		$tagihan = $tagihan->get_list($keyword, $wil_id, $bulan, $tahun, $tag_status);
		if(empty($tagihan))
		{
			$response['status'] = "error";
			$response['message'] = "Tagihan not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $tagihan;

		// return json response
		return response()->json($response);
	}

	/*==  List Count Total Tagihan==*/
	public function list_count(Request $request, Tagihan $tagihan)
	{
		$wil_id = $request->wil_id;
		$keyword = $request->keyword;
		$bulan = $request->bulan;
		$tahun = $request->tahun;

		$tagihan = $tagihan->get_list_count($keyword, $wil_id, $bulan, $tahun);
		if(empty($tagihan))
		{
			$response['status'] = "error";
			$response['message'] = "Tagihan not found";
			return response()->json($response);
			exit();
		}

		$results = array(
			"tag_count" => $tagihan->tag_count,
		);

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*==  Detail ==*/
	public function detail($id, Request $request, Tagihan $tagihan)
	{
		// get data
		$tag = $tagihan->get_detail($id);
		if(empty($tag))
		{
			$response['status'] = "error";
			$response['message'] = "Tagihan not found";
			return response()->json($response);
			exit();
		}

		$results = array(
			"tag_id" => $tag->tag_id,
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tag;

		// return json response
		return response()->json($response);
	}


	/*==  Detail Tagihan JT ==*/
	public function detail_jt($id, Request $request, Tagihan $tagihan)
	{
		// get data
		$tag = $tagihan->get_detail_jt($id);
		if(empty($tag))
		{
			$response['status'] = "error";
			$response['message'] = "Detil Tagihan not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tag;

		// return json response
		return response()->json($response);
	}

	/*==  Detail Tagihan JTI ==*/
	public function detail_jti($id, Request $request, Tagihan $tagihan)
	{
		// get data
		$tag = $tagihan->get_detail_jti($id);
		if(empty($tag))
		{
			$response['status'] = "error";
			$response['message'] = "Detil Tagihan not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $tag;

		// return json response
		return response()->json($response);
	}

	/*== List Add ==*/
	public function add_jenis(Request $request)
	{
		// data
		$wil_id = $request->wil_id;
		$jt_jenis = $request->jt_jenis;
		$jt_tarif_nominal = $request->jt_tarif_nominal;
		$jt_tarif_satuan = $request->jt_tarif_satuan;

		// validate param
		if($jt_jenis=='')
		{
			$response['status'] = "error";
			$response['message'] = "Jenis are required fields";
			return response()->json($response);
			exit();
		}

		$jt = new Jt;

		//set data pengurus
		$jt->wil_id = $wil_id;
		$jt->jt_jenis = $jt_jenis;
		$jt->jt_tarif_nominal = $jt_tarif_nominal;
		$jt->jt_tarif_satuan = $jt_tarif_satuan;
		$jt->save();

		$results = array(
			"jt_jenis" => $jt_jenis
		);

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*== Update Jenis ==*/
	public function update_jenis(Request $request)
	{
		// data
		$jt_id = $request->jt_id;
		$jt_jenis = $request->jt_jenis;
		$jt_tarif_nominal = $request->jt_tarif_nominal;
		$jt_tarif_satuan = $request->jt_tarif_satuan;

		$jt = Jt::find($jt_id);

		//set data
		$jt->jt_id = $jt_id;
		$jt->jt_jenis = $jt_jenis;
		$jt->jt_tarif_nominal = $jt_tarif_nominal;
		$jt->jt_tarif_satuan = $jt_tarif_satuan;
		$jt->save();

		$results = array(
			"jt_jenis" => $jt_jenis
		);

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*==  List Jenis ==*/
	public function list_jenis($wil_id, Request $request, Jt $jt)
	{
		// get data
		$jt = $jt->get_list($wil_id);
		if(empty($jt))
		{
			$response['status'] = "error";
			$response['message'] = "Jenis Tagihan not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $jt;

		// return json response
		return response()->json($response);
	}

	//jenis tagihan insidental

	/*== List Add ==*/
	public function add_jenis_insidental(Request $request)
	{
		// data
		$wil_id = $request->wil_id;
		$jti_nama = $request->jti_nama;
		$jti_nominal = $request->jti_nominal;
		$jti_tanggal = date('Y-m-d');

		// validate param
		if($jti_nama=='')
		{
			$response['status'] = "error";
			$response['message'] = "Jenis are required fields";
			return response()->json($response);
			exit();
		}

		$jti = new Jti;

		//set data pengurus
		$jti->wil_id = $wil_id;
		$jti->jti_nama = $jti_nama;
		$jti->jti_nominal = $jti_nominal;
		$jti->jti_tanggal = $jti_tanggal;
		$jti->save();

		$results = array(
			"jti_nama" => $jti_nama
		);

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*== Update Jenis Insidental ==*/
	public function update_jenis_insidental(Request $request)
	{
		// data
		$jti_id = $request->jti_id;
		$jti_nama = $request->jti_nama;
		$jti_nominal = $request->jti_nominal;

		$jti = Jti::find($jti_id);

		//set data
		$jti->jti_id = $jti_id;
		$jti->jti_nama = $jti_nama;
		$jti->jti_nominal = $jti_nominal;
		$jti->save();

		$results = array(
			"jti_nama" => $jti_nama
		);

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*==  List Jenis Insidental ==*/
	public function list_jenis_insidental($wil_id, Request $request, Jti $jti)
	{
		// get data
		$jti = $jti->get_list($wil_id);
		if(empty($jti))
		{
			$response['status'] = "error";
			$response['message'] = "Jenis Tagihan Insidental not found";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $jti;

		// return json response
		return response()->json($response);
	}


	//detil tagihan
	/*== Update Jenis ==*/
	public function update_detil_jenis(Request $request)
	{
		// data
		$dt_id = $request->dt_id;
		$dt_qty = $request->dt_qty;
		$jt_id = $request->jt_id;

		//total nominal
		//find jenis

		$jt = Jt::where([
			['jt_id',$jt_id]
		])->first();

		$jt_tarif_nominal = $jt->jt_tarif_nominal;
		$dt_nominal = ($dt_qty*$jt_tarif_nominal);

		$dt = Dt::find($dt_id);

		//set data
		$dt->dt_id = $dt_id;
		$dt->dt_qty = $dt_qty;
		$dt->dt_nominal = $dt_nominal;
		$dt->save();

		$results = array(
			"dt_qty" => $dt_qty
		);

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*== Update Jenis Insidental ==*/
	public function update_detil_jenis_insidental(Request $request)
	{
		// data
		$dt_id = $request->dt_id;
		$dt_qty = $request->dt_qty;
		$jti_id = $request->jti_id;

		//total nominal
		//find jenis

		$jti = Jti::where([
			['jti_id',$jti_id]
		])->first();

		$jti_nominal = $jti->jt_nominal;
		$dt_nominal = ($dt_qty*$jti_nominal);

		$dt = Dt::find($dt_id);

		//set data
		$dt->dt_id = $dt_id;
		$dt->dt_qty = $dt_qty;
		$dt->dt_nominal = $dt_nominal;
		$dt->save();

		$results = array(
			"dt_qty" => $dt_qty
		);

		// response
		$response['status'] = "success";
		$response['message'] = "Success";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

}
