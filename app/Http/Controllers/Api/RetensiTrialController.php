<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\RetensiTrial;
//use DataTables;
use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class RetensiTrialController extends Controller
{
    
    
	/*==  Detail ==*/
	public function detail(Request $request, RetensiTrial $trial)
	{
		// get data
		$info = RetensiTrial::orderBy('global_id', 'asc')->get();
        return $info;
	}

	

	/*== Update ==*/
	public function updateTrial(Request $request)
	{


		$trial = RetensiTrial::find(1);

        $trial->global_value = $request->trial;
        
        $trial->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $request->trial;

		// return json response
		return response()->json($response);
	}
	/*== Update ==*/
	public function updateRetensi(Request $request)
	{


		$trial = RetensiTrial::find(2);

        $trial->global_value = $request->retensi;
        
        $trial->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $request->retensi;

		// return json response
		return response()->json($response);
	}
}