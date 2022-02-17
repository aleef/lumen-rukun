<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Notifikasi;

class NotifikasiController extends Controller
{

    public function list(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;
        if(empty($warga_id)) {
            $response['message'] = 'ID Warga tidak ada';
            return response()->json($response);
        }

        $limit = empty($request->limit) ? 20 : $request->limit;
        $page = $request->page;


        if(!empty($page)) {
            $list = Notifikasi::where('warga_id',$warga_id)
                            ->orderByDesc('notif_id')
                            ->limit($limit)->offset(($page-1)*$limit);
        }else {
            $list = Notifikasi::where('warga_id',$warga_id)
                            ->orderByDesc('notif_id');
        }
        $list = $list->get();

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $list;

		// return json response
		return response()->json($response);
    }

    public function doRead(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $notif_id = $request->notif_id;
        if(empty($notif_id)) {
            $response['message'] = 'ID Notif tidak ada';
            return response()->json($response);
        }

        $notifikasi = Notifikasi::findOrFail($notif_id);
        $notifikasi->notif_id = $notif_id;
        $notifikasi->is_read = 'Y';
        $notifikasi->save();

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $notifikasi;

		// return json response
		return response()->json($response);
    }

    public function hasNotif(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;
        if(empty($warga_id)) {
            $response['message'] = 'ID Warga tidak ada';
            return response()->json($response);
        }

        $totalNotif = Notifikasi::where('warga_id',$warga_id)
                        ->whereNull('is_read')
                        ->count();
        $response['status'] = "success";
		$response['message'] = "OK";
		$response['totalNotif'] = $totalNotif;

        // return json response
		return response()->json($response);
    }
}
