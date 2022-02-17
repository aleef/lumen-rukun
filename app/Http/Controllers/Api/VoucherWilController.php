<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Voucher;
use App\VoucherWil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Response;

class VoucherWilController extends Controller
{

    public function list(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        if(empty($wil_id)) {
            $response['message'] = "Wilayah ID tidak boleh kosong";
            return response()->json($response);
        }

        $list = VoucherWil::where('wil_id',$wil_id)
                ->where('vw_status','0')
                ->whereRaw("vw_berlaku_sd >= '".date('Y-m-d')."'")
                ->orderBy('vw_id','asc')
                ->get();

        $data = array();
        $i = 0;

        foreach($list as $item) {

            $vc = Voucher::find($item->v_id);

            $data[$i] = json_decode(json_encode($item), true);
            $data[$i]['v_nominal'] = empty($vc->v_nilai_nominal) ? $vc->v_nilai_persen."%" : "Rp.".number_format($vc->v_nilai_nominal,0,',','.');
            $data[$i]['v_kode'] = $vc->v_kode;
            $data[$i]['v_ket'] = $vc->v_ket;
            $data[$i]['vw_berlaku_sd'] = Carbon::parse($item->vw_berlaku_sd)->isoFormat('D MMMM Y');

            $i++;
        }


        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $data;

		// return json response
		return response()->json($response);
    }

}
