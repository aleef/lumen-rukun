<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\JadwalBuka;
use Illuminate\Http\Request;
use Response;

class JadwalBukaController extends Controller
{

    public function list(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $usaha_id = $request->usaha_id;
        $list = JadwalBuka::where('usaha_id',$usaha_id)->first();

        // response
        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $list;

		// return json response
		return response()->json($response);
    }


    public function edit(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $jb_id = $request->jb_id;
        $jadwalBuka = JadwalBuka::find($jb_id);

        $jadwalBuka->jb_id = $jb_id;
        $jadwalBuka->jb_ming_libur = $request->jb_ming_libur;
        $jadwalBuka->jb_ming_buka  = $request->jb_ming_buka;
        $jadwalBuka->jb_ming_tutup = $request->jb_ming_tutup;
        $jadwalBuka->jb_sen_libur  = $request->jb_sen_libur;
        $jadwalBuka->jb_sen_buka   = $request->jb_sen_buka;
        $jadwalBuka->jb_sen_tutup  = $request->jb_sen_tutup;
        $jadwalBuka->jb_sel_libur  = $request->jb_sel_libur;
        $jadwalBuka->jb_sel_buka   = $request->jb_sel_buka;
        $jadwalBuka->jb_sel_tutup  = $request->jb_sel_tutup;
        $jadwalBuka->jb_rab_libur  = $request->jb_rab_libur;
        $jadwalBuka->jb_rab_buka   = $request->jb_rab_buka;
        $jadwalBuka->jb_rab_tutup  = $request->jb_rab_tutup;
        $jadwalBuka->jb_kam_libur  = $request->jb_kam_libur;
        $jadwalBuka->jb_kam_buka   = $request->jb_kam_buka;
        $jadwalBuka->jb_kam_tutup  = $request->jb_kam_tutup;
        $jadwalBuka->jb_jum_libur  = $request->jb_jum_libur;
        $jadwalBuka->jb_jum_buka   = $request->jb_jum_buka;
        $jadwalBuka->jb_jum_tutup  = $request->jb_jum_tutup;
        $jadwalBuka->jb_sab_libur  = $request->jb_sab_libur;
        $jadwalBuka->jb_sab_buka   = $request->jb_sab_buka;
        $jadwalBuka->jb_sab_tutup  = $request->jb_sab_tutup;
        $jadwalBuka->save();


        // response
        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $jadwalBuka;

		// return json response
		return response()->json($response);

    }

}
