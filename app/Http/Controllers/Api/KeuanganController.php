<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Keuangan;
use App\Wilayah;
use Carbon\Carbon;
use DB;

class KeuanganController extends Controller
{
	private $ctrl = 'keuangan';
	private $title = 'Keuangan';

	/*==  List ==*/
	public function list(Request $request, Keuangan $keuangan)
	{

		$wil_id = $request->wil_id;
		$keyword = $request->keyword;
		$status = $request->status;

		// get data
		$keuangan = $keuangan->get_list($keyword, $wil_id, $status);
		if(empty($keuangan))
		{
			$date_list = array(
				"keu_sumbertujuan" => null,
				"keu_nominal" => null,
			);

			$results = array(
				"keu_tgl" => null,
				"total_pemasukan" => null,
				"total_pengeluaran" => null,
				"date_list" => array($date_list)
			);

			$response['status'] = "error";
			$response['message'] = "Keuangan not found";
			$response['results'] = array($results);

			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $keuangan;

		// return json response
		return response()->json($response);
	}


    public function list_daily(Request $request)
	{

		$wil_id = $request->wil_id;
        $month = $request->month;
        $year = $request->year;

        $joinTxt = $year."-";
        $joinTxt .= ($month < 10) ? "0".$month : $month;

        $listMonthly = Keuangan::where('wil_id', $wil_id)
                        ->where(DB::raw('keu_tgl_short::text'),'like',DB::raw("'".$joinTxt."%'"))
                        ->orderBy('keu_tgl','desc')
                        ->get();

        $data = [];
        $idx = 0;

        $dateShort = "xx";
        $total_uang_masuk = 0;
        $total_uang_keluar = 0;
        $masterIdx = 0;

        foreach($listMonthly as $item) {
            if($item->keu_tgl_short != $dateShort) {
                $masterIdx = $idx;
                $dateShort = $item->keu_tgl_short;

                //data master
                $data[$idx]['keu_id'] =  null;
                $data[$idx]['tgl'] = Carbon::parse($item->keu_tgl_short)->isoFormat('DD');
                $data[$idx]['hari'] = Carbon::parse($item->keu_tgl_short)->isoFormat('dddd');
                $data[$idx]['bulan'] = Carbon::parse($item->keu_tgl_short)->isoFormat('MMMM');
                $data[$idx]['uang_masuk'] = ($item->keu_status == '1') ? $item->keu_nominal : 0;
                $data[$idx]['uang_keluar'] = ($item->keu_status == '0') ? $item->keu_nominal : 0;
                $idx+= 1;
                $data[$idx] = json_decode(json_encode($item), true);

                $total_uang_masuk += ($item->keu_status == '1') ? $item->keu_nominal : 0;
                $total_uang_keluar += ($item->keu_status == '0') ? $item->keu_nominal : 0;
            }else {
                $data[$idx] = json_decode(json_encode($item), true);
                $data[$masterIdx]['uang_masuk'] += ($item->keu_status == '1') ? $item->keu_nominal : 0;
                $data[$masterIdx]['uang_keluar'] += ($item->keu_status == '0') ? $item->keu_nominal : 0;

                $total_uang_masuk += ($item->keu_status == '1') ? $item->keu_nominal : 0;
                $total_uang_keluar += ($item->keu_status == '0') ? $item->keu_nominal : 0;

            }
            $idx++;
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['total_uang_masuk'] = $total_uang_masuk;
        $response['total_uang_keluar'] = $total_uang_keluar;
        $response['saldo'] = $total_uang_masuk - $total_uang_keluar;
        $response['results'] = $data;

		// return json response
		return response()->json($response);
	}

    public function list_monthly(Request $request)
	{

		$wil_id = $request->wil_id;
        $year = $request->year;

        $listMonth = array(
            array('month' => '01','name' => 'Januari', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '02','name' => 'Februari', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '03','name' => 'Maret', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '04','name' => 'April', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '05','name' => 'Mei', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '06','name' => 'Juni', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '07','name' => 'Juli', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '08','name' => 'Agustus', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '09','name' => 'September', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '10','name' => 'Oktober', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '11','name' => 'November', 'uang_masuk' => 0, 'uang_keluar' => 0),
            array('month' => '12','name' => 'Desember', 'uang_masuk' => 0, 'uang_keluar' => 0),
        );

        $total_uang_masuk = 0;
        $total_uang_keluar = 0;

        $i = 0;

        foreach($listMonth as $item) {
            $searchPeriode = $year."-".$item['month'];
            $itemMasuk = Keuangan::where('wil_id',$wil_id)
                                   ->where(DB::raw('keu_tgl_short::text'),'like',DB::raw("'".$searchPeriode."%'"))
                                   ->where('keu_status','1')
                                   ->select(DB::raw('sum(keu_nominal) as nominal'))
                                   ->first();

            $uang_masuk = 0;
            if(!empty($itemMasuk)) {
                $total_uang_masuk += empty($itemMasuk->nominal) ? 0 : $itemMasuk->nominal;
                $uang_masuk = empty($itemMasuk->nominal) ? 0 : $itemMasuk->nominal;
            }

            $itemKeluar = Keuangan::where('wil_id',$wil_id)
                                ->where(DB::raw('keu_tgl_short::text'),'like',DB::raw("'".$searchPeriode."%'"))
                                ->where('keu_status','0')
                                ->select(DB::raw('sum(keu_nominal) as nominal'))
                                ->first();

            $uang_keluar = 0;
            if(!empty($itemKeluar)) {
                $total_uang_keluar += empty($itemKeluar->nominal) ? 0 : $itemKeluar->nominal;
                $uang_keluar = empty($itemKeluar->nominal) ? 0 : $itemKeluar->nominal;
            }

            $data[$i] = $item;
            $data[$i]['uang_masuk'] = $uang_masuk;
            $data[$i]['uang_keluar'] = $uang_keluar;

            $i++;

        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['total_uang_masuk'] = $total_uang_masuk;
        $response['total_uang_keluar'] = $total_uang_keluar;
        $response['saldo'] = $total_uang_masuk - $total_uang_keluar;
        $response['results'] = $data;

		// return json response
		return response()->json($response);
	}

    public function list_yearly(Request $request)
	{

		$wil_id = $request->wil_id;
        $listYear = Keuangan::where('wil_id',$wil_id)
                            ->select(DB::raw("distinct(to_char(keu_tgl_short,'yyyy')) as tahun"))
                            ->get();

        $i = 0;
        $total_uang_masuk = 0;
        $total_uang_keluar = 0;
        $data = [];
        foreach($listYear as $item) {
            $data[$i]['year'] = $item->tahun;
            $itemMasuk = Keuangan::where('wil_id',$wil_id)
                                   ->where(DB::raw('keu_tgl_short::text'),'like',DB::raw("'".$item->tahun."%'"))
                                   ->where('keu_status','1')
                                   ->select(DB::raw('sum(keu_nominal) as nominal'))
                                   ->first();


            if(!empty($itemMasuk)) {
                $total_uang_masuk += empty($itemMasuk->nominal) ? 0 : $itemMasuk->nominal;
                $data[$i]['uang_masuk'] = empty($itemMasuk->nominal) ? 0 : $itemMasuk->nominal;
            }

            $itemKeluar = Keuangan::where('wil_id',$wil_id)
            ->where(DB::raw('keu_tgl_short::text'),'like',DB::raw("'".$item->tahun."%'"))
            ->where('keu_status','0')
            ->select(DB::raw('sum(keu_nominal) as nominal'))
            ->first();

            if(!empty($itemKeluar)) {
                $total_uang_keluar += empty($itemKeluar->nominal) ? 0 : $itemKeluar->nominal;
                $data[$i]['uang_keluar'] = empty($itemKeluar->nominal) ? 0 : $itemKeluar->nominal;
            }

            $i++;
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['total_uang_masuk'] = $total_uang_masuk;
        $response['total_uang_keluar'] = $total_uang_keluar;
        $response['saldo'] = $total_uang_masuk - $total_uang_keluar;
        $response['results'] = $data;

		// return json response
		return response()->json($response);
	}


	/*==  Detail ==*/
	public function detail(Request $request)
	{
        $keu_id = $request->keu_id;
		// get data
		$keuangan = Keuangan::findOrFail($keu_id);
        $keuangan->keu_tgl_short = Carbon::parse($keuangan->keu_tgl_short)->format('d/m/Y');

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $keuangan;

		// return json response
		return response()->json($response);
	}

    /*==  Detail ==*/
	public function delete(Request $request)
	{
        $keu_id = $request->keu_id;
        // get data
		$keuangan = Keuangan::findOrFail($keu_id);
        $wil_id = $keuangan->wil_id;

        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $keuangan->delete();
		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}



	/*== Add==*/
	public function add_masuk_keluar(Request $request)
	{
		//
		$wil_id = $request->wil_id;
        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }


		$keu_tgl = $request->keu_tgl;
		$keu_status = $request->keu_status;
		$keu_sumbertujuan = $request->keu_sumbertujuan;
		$keu_deskripsi = $request->keu_deskripsi;
		$keu_nominal = $request->keu_nominal;

        $tglArr = explode("/",$keu_tgl);
        $tgl = $tglArr[2]."-".$tglArr[1]."-".$tglArr[0];

        $tgl_catat = strtotime($tgl);
        $tgl_catat = date('Y-m-d',$tgl_catat);

        Keuangan::create([
            'wil_id' => $wil_id,
            'keu_tgl' => $tgl_catat,
            'keu_tgl_short' => $tgl_catat,
            'keu_status' => $keu_status,
            'keu_sumbertujuan' => $keu_sumbertujuan,
            'keu_deskripsi' => $keu_deskripsi,
            'keu_nominal' => $keu_nominal,
            'created_at' => Carbon::now(),
        ]);

        $response = array();
		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}

    public function edit_masuk_keluar(Request $request)
	{

        $keu_id = $request->keu_id;
		$keu_tgl = $request->keu_tgl;
		$keu_status = $request->keu_status;
		$keu_sumbertujuan = $request->keu_sumbertujuan;
		$keu_deskripsi = $request->keu_deskripsi;
		$keu_nominal = $request->keu_nominal;

        $tglArr = explode("/",$keu_tgl);
        $tgl = $tglArr[2]."-".$tglArr[1]."-".$tglArr[0];

        $tgl_catat = strtotime($tgl);
        $tgl_catat = date('Y-m-d',$tgl_catat);

        $keuangan = Keuangan::find($keu_id);
        $wil_id = $keuangan->wil_id;

        $response = array('status' => 'failed', 'message' => '');
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }


        $keuangan->keu_id = $keu_id;
        $keuangan->keu_tgl = $tgl_catat;
        $keuangan->keu_tgl_short = $tgl_catat;
        $keuangan->keu_status = $keu_status;
        $keuangan->keu_sumbertujuan = $keu_sumbertujuan;
        $keuangan->keu_deskripsi = $keu_deskripsi;
        $keuangan->keu_nominal = $keu_nominal;
        $keuangan->save();

        $response = array();
		// response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
	}

    /*==  Detail ==*/
	public function range_report(Request $request)
	{
        $wil_id = $request->wil_id;
		$start_date = $request->start_date;
        $end_date = $request->end_date;

        $startArr = explode("/",$start_date);
        $start_date = $startArr[2]."-".$startArr[1]."-".$startArr[0];

        $endArr = explode("/",$end_date);
        $end_date = $endArr[2]."-".$endArr[1]."-".$endArr[0];

        $list_uang_masuk = Keuangan::where('wil_id', $wil_id)
                ->where('keu_status','1')
                ->whereBetween('keu_tgl_short',[date($start_date), date($end_date)])
				->orderBy('keu_tgl','desc')
                ->select('keu_id',DB::raw("to_char(keu_tgl_short,'dd/mm/yyyy') as keu_tgl_short"),'keu_nominal','keu_status','keu_sumbertujuan','keu_deskripsi')
                ->get();

        $list_uang_keluar = Keuangan::where('wil_id', $wil_id)
        ->where('keu_status','0')
        ->whereBetween('keu_tgl_short',[date($start_date), date($end_date)])
		->orderBy('keu_tgl','desc')
        ->select('keu_id',DB::raw("to_char(keu_tgl_short,'dd/mm/yyyy') as keu_tgl_short"),'keu_nominal','keu_status','keu_sumbertujuan','keu_deskripsi')
        ->get();

        $total_uang_masuk = 0;
        $total_uang_keluar = 0;

        foreach($list_uang_masuk as $item) {
            $total_uang_masuk += $item->keu_nominal;
        }

        foreach($list_uang_keluar as $item) {
            $total_uang_keluar += $item->keu_nominal;
        }

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['list_uang_masuk'] = $list_uang_masuk;
        $response['list_uang_keluar'] = $list_uang_keluar;
        $response['total_uang_masuk'] = $total_uang_masuk;
        $response['total_uang_keluar'] = $total_uang_keluar;
        $response['saldo'] = $total_uang_masuk - $total_uang_keluar;


		// return json response
		return response()->json($response);
	}

    /*==  Detail ==*/
	public function search_list(Request $request)
	{
        $wil_id = $request->wil_id;
		$keyword = $request->keyword;

        $list = Keuangan::where('wil_id', $wil_id);
        $list = $list->where(function($q) use ($keyword) {
            $q->where('keu_sumbertujuan','ilike',"%$keyword%")
                ->orWhere('keu_deskripsi','ilike',"%$keyword%");

        });
        $list = $list->select('keu_id',DB::raw("to_char(keu_tgl_short,'dd/mm/yyyy') as keu_tgl_short"),'keu_nominal','keu_status','keu_sumbertujuan','keu_deskripsi')
                ->orderBy('keu_tgl','desc')
                ->get();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $list;


		// return json response
		return response()->json($response);
	}




	/*== Add==*/
	public function add(Request $request)
	{
		//
		$wil_id = $request->wil_id;
		$keu_tgl = $request->keu_tanggal;
		$keu_status = $request->keu_status;
		$keu_sumbertujuan = $request->keu_sumbertujuan;
		$keu_deskripsi = $request->keu_deskripsi;
		$keu_nominal = $request->keu_nominal;

		$tgl_catat = strtotime($keu_tgl);
        $tgl_catat = date('Y-m-d',$tgl_catat);

        Keuangan::create([
            'wil_id' => $wil_id,
            'keu_tgl' => $tgl_catat,
            'keu_tgl_short' => $tgl_catat,
            'keu_status' => $keu_status,
            'keu_sumbertujuan' => $keu_sumbertujuan,
            'keu_deskripsi' => $keu_deskripsi,
            'keu_nominal' => $keu_nominal
        ]);

        $results = array(
            'keu_tgl' => $tgl_catat,
            'keu_tgl_short' => $tgl_catat,
        );

        $response = array();
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
		//
		$keu_id = $request->keu_id;
		$wil_id = $request->wil_id;
		$keu_tgl = $request->keu_tanggal;
		$keu_status = $request->keu_status;
		$keu_sumbertujuan = $request->keu_sumbertujuan;
		$keu_deskripsi = $request->keu_deskripsi;
		$keu_nominal = $request->keu_nominal;


		$keuangan = Keuangan::find($keu_id);

		$tgl_catat = strtotime($keu_tgl);
        $tgl_catat = date('Y-m-d',$tgl_catat);

		$keuangan->keu_id = $keu_id;
		$keuangan->wil_id = $wil_id;
		$keuangan->keu_tgl = $tgl_catat;
        $keuangan->keu_tgl_short = $tgl_catat;
		$keuangan->keu_status = $keu_status;
		$keuangan->keu_sumbertujuan = $keu_sumbertujuan;
		$keuangan->keu_deskripsi = $keu_deskripsi;
		$keuangan->keu_nominal = $keu_nominal;
		$keuangan->save();

		$results = array(
			"keu_tgl" => $keu_tgl,
			"keu_nominal" => $keu_nominal
		);

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

	/*== Total ==*/
	public function total(Request $request, Keuangan $keuangan)
	{
		$wil_id = $request->wil_id;
		$status = $request->status;

		$keuangan = $keuangan->get_total($wil_id, $status);
		if(empty($keuangan))
		{
			$results = array(
				"saldo" => "Rp 0",
				"total_pemasukan" => "Rp 0",
				"total_pengeluaran" => "Rp 0",
			);

			$response['status'] = "error";
			$response['message'] = "Keuangan not found";
			$response['results'] = $results;

			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $keuangan;

		// return json response
		return response()->json($response);
	}

	/*==  List Laporan Keuangan ==*/
	public function list_lk(Request $request, Keuangan $keuangan)
	{

		$wil_id = $request->wil_id;
		$dari = $request->dari;
		$sampai = $request->sampai;

		// get data
		$keuangan = $keuangan->get_list_lk($wil_id, $dari, $sampai);
		//print_r($keuangan);
		if(empty($keuangan))
		{
			$pemasukan_list = array(
				"keu_sumbertujuan" => "Rp 0",
				"keu_nominal" => "Rp 0",
			);

			$pengeluaran_list = array(
				"keu_sumbertujuan" => "Rp 0",
				"keu_nominal" => "Rp 0",
			);

			$results = array(
				"saldo_nominal" => "0",
				"saldo" => "Rp 0",
				"total_pemasukan" => "Rp 0",
				"list_pemasukan" => array($pemasukan_list),
				"total_pengeluaran" => "Rp 0",
				"list_pengeluaran" => array($pengeluaran_list)
			);

			$response['status'] = "error";
			$response['message'] = "Laporan Keuangan not found";
			$response['results'] = $results;

			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $keuangan;

		// return json response
		return response()->json($response);
	}
}
