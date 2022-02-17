<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\JenisUsaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;

class JenisUsahaController extends Controller
{

    public function list(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $list = JenisUsaha::orderBy('ju_id','asc')->get();

        // response
        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $list;

		// return json response
		return response()->json($response);
    }
    public function listcrm(Request $request) {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $length = $request->get("length");
            $search = $request->get('search')['value'];
    
            $order =  $request->get('order');
    
             $col = 0;
             $dir = "";
             if(!empty($order)) {
                 foreach($order as $o) {
                     $col = $o['column'];
                     $dir= $o['dir'];
                 }
            }
    
             if($dir != "asc" && $dir != "desc") {
                 $dir = "asc";
             }
             $columns_valid = array("ju_nama");
             if(!isset($columns_valid[$col])) {
                $order = null;
            } else {
                $order = $columns_valid[$col];
            }
            //$info = $jenisUsaha->get_list($start, $length, $order, $dir, $search);
            $res =  DB::table('jenis_usaha as a');
            if($search!=''){
                $res = $res->where('a.ju_nama','ilike',"%$search%");
            }            
            if(isset($order)){
                $res = $res->orderBy($order);
            }else{
                $order = $res->orderBy('a.ju_id');
            }
            if(isset($length) || isset($start)){
                $res = $res->skip($start)->take($length);
            }
            $res = $res->get();
            $i = 1;
            //$data[] =array();
            //$data[] = '';
             foreach($res as $r) {
                 
                 $data[] = array(
                     $i,
                     $r->ju_nama,
                     '<form action="jenisUsaha/'.$r->ju_id.'/destroy" method="POST"><a href="jenisUsaha/'.$r->ju_id.'/edit"><i class="fa fa-edit fa-lg text-success"></i></a> <a href="#" id="hapus" data-id="'.$r->ju_id.'" data-nama="'.$r->ju_nama.'"><i class="fa fa-trash fa-lg text-danger"></i></a></form>'
                 );
                 $i++;
             }
    
             //total data lead
            $total_sal = JenisUsaha::count();
            //total filtered
            $total_fil = DB::table('jenis_usaha as a');
            if($search!=''){
                 $total_fil = $total_fil->where('a.ju_nama','ilike',"%$search%");
             }
             $total_fil = $total_fil->count();
    
             /*$output = array(
                 "draw" => $draw,
                 "recordsTotal" => $total_sal,
                 "recordsFiltered" => $total_fil,
                 "data" => $data
             );*/
             
            if(empty($res) || $res =='' || $data =='' || $data == false)
            {
                $output = array(
                    "draw" => 1,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => ''
                );
                //return response()->json($response);
                exit();
            }else{
                $output = array(
                    "draw" => $draw,
                    "recordsTotal" => $total_sal,
                    "recordsFiltered" => $total_fil,
                    "data" => $data
                );
            }
             // return json response
             return response()->json($output, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        
    }
    /*==  Detail ==*/
	public function detail($id, Request $request)
	{
		// get data
		$info = JenisUsaha::find($id);
        return $info;
	}
    /*== Add ==*/
	public function add(Request $request)
	{
        try{
            
            $ju = new JenisUsaha();
            $ju->ju_nama = $request->ju_nama;
            
            $ju->save();
            
            // response
            $response['status'] = "success";
            $response['message'] = "OK";
            $response['results'] = "Sukses menyimpan jenis usaha baru";
            // return json response
            return response()->json($response);
         }
         catch(\Exception $e){
             // response
             $response['status'] = "error";
             $response['message'] = "error";
             $response['results'] =  $e->getMessage();
             // return json response
             return response()->json($response);
         }

	}
	

	/*== Update ==*/
	public function update(Request $request)
	{
		
		// account warga
		$ju_id = $request->ju_id;

		$ju = JenisUsaha::find($ju_id);

        $ju->ju_nama = $request->ju_nama;
        
        $ju->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses mengubah data Jenis Usaha ".$request->ju_nama;

		// return json response
		return response()->json($response);
	}

	/*== Delete ==*/
	public function delete(Request $request)
	{
		$ju_id = $request->ju_id;

		// get data
		$info = JenisUsaha::find($ju_id);
		// theme checking
		if(empty($info))
		{
			$response['status'] = "error";
			$response['message'] = "Jenis Usaha dengan ID : $ju_id tidak ditemukan";
			return response()->json($response);
			exit();
		}

		try
		{
			// delete
			JenisUsaha::find($ju_id)->delete();
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Gagal menghapus data jenis usaha";
			return response()->json($response);
			exit();
		}

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		//$response['results'] = $results;

		// return json response
		return response()->json($response);
	}

}
