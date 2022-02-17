<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Marketing;
use App\Sales;
use App\Wilayah;
use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class MarketingController extends Controller
{

    
    public function list(Request $request, Marketing $mar)
    {
        {
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
			 $columns_valid = array("wil_nama", "wil_mulai_trial");
			 if(!isset($columns_valid[$col])) {
				$order = null;
			} else {
				$order = $columns_valid[$col];
			}
			$rs = DB::table("marketing as a")
            ->select('a.*', 'b.wil_nama', 'b.wil_alamat', 'c.sales_nama', 'c.sales_id')
            ->join('wilayah as b','b.wil_id','=','a.wil_id')
            ->join('sales as c','c.sales_id','=','a.sales_id');

       

			if($search!=''){
				$rs = $rs->where(function($q) use ($search) {
						$q->where('b.wil_nama','ilike',"%$search%")
						->orWhere('c.sales_nama','ilike',"%$search%");
				});
			}

			if($length != 0) {
				$rs = $rs->limit($length);
			}
			if(isset($order)){
				$rs = $rs->orderBy($order);
			}

			$rs = $rs->get();

			$i = 1;
			$data =array();
			if(!empty($rs)){
				foreach($rs as $r) {
					
					$data[] = array(
						$r->wil_nama,
						$r->wil_alamat,
						$r->sales_nama,
						(Carbon::parse($r->mar_mulai_handle)->format('d-m-Y') ),
						$r->mar_status,
						'<form action="wilayah/'.$r->wil_id.'/destroy" method="POST"> <a href="penjualan/'.$r->mar_id.'/edit"><i class="fa fa-edit fa-lg text-success" title="Edit"></i></a> <a href="#" id="hapus" data-id="'.$r->mar_id.'" data-nama="'.$r->wil_nama.'"><i class="fa fa-trash fa-lg text-danger" title="Hapus"></i></a></form>'
					);
					$i++;
				}
				//total data lead
			   $total_sal = Marketing::count();
			   //total filtered
			   $total_fil = DB::table('marketing as a')
			   			->select('a.*', 'b.wil_nama', 'b.wil_alamat', 'c.sales_nama', 'c.sales_id')
						->join('wilayah as b','b.wil_id','=','a.wil_id')
						->join('sales as c','c.sales_id','=','a.sales_id');
			   if($search!=''){
					$total_fil = $total_fil->where('b.wil_nama','ilike',"%$search%")
					->orWhere('c.sales_nama','ilike',"%$search%");
				}
				$total_fil = $total_fil->count();
	   
				$output = array(
					"draw" => $draw,
					"recordsTotal" => $total_sal,
					"recordsFiltered" => $total_fil,
					"data" => $data
				);
			}else{
				$output = array(
					"draw" => $draw,
					"recordsTotal" => 0,
					"recordsFiltered" => 0,
					"data" => $data
				);
			}
	
			/*if(empty($info) || $info ='')
			{
				$response['status'] = "error";
				$response['message'] = "Wilayah not found";
				return response()->json($response);
				exit();
			}*/
	
			
			/*$result = Wilayah::where('wilayah.wil_head', '1')->latest()->get();
			 $response['status'] = "success";
			 $response['message'] = "OK";
			 $response['results'] = $result;*/
	 
			 // return json response
			 return response()->json($output, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
			
		}
        
    }
    
    
	/*==  Detail ==*/
	public function detail($mar_id)
	{
		// get data
		$info = Marketing::find($mar_id);
		/*if(empty($info))  
		{
			$response['status'] = "error";
			$response['message'] = "Informasi not found";
            $response['results'] = [];
			return response()->json($response);
			exit();
		}

	
		$results = array(
			"sales_nama" => $mar->sales_nama,
			"sales_hp" => $mar->sales_hp,
			"sales_email" => $mar->sales_email,
			"sales_kode" => $mar->sales_kode,
			"sales_parent_id" => $mar->sales_parent_id,
			"sales_head" => $mar->sales_head,
		);

		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;*/

		// return json response
		//return response()->json($response);
		return $info;
	}

	/*== Add ==*/
	public function add(Request $request)
	{
        $mar = new Marketing;
        $mar->sales_id = $request->sales_id;
        $mar->wil_id = $request->wil_id;
        $mar->mar_mulai_handle = $request->mar_mulai_handle;
        $mar->mar_status = $request->mar_status;
        $mar->mar_fee = $request->mar_fee;
        
        $mar->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses menyimpan data penjualan baru";

		// return json response
		return response()->json($response);
	}
	

	/*== Update ==*/
	public function update(Request $request)
	{
		// account warga
		$mar_id = $request->mar_id;


		$mar = Marketing::find($mar_id);
        
        $mar->sales_id = $request->sales_id;
        $mar->wil_id = $request->wil_id;
        $mar->mar_mulai_handle = $request->mar_mulai_handle;
        $mar->mar_status = $request->mar_status;
        $mar->mar_fee = $request->mar_fee;
        
        $mar->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses mengubah data penjualan ".$request->sales_nama;

		// return json response
		return response()->json($response);
	}
	
	/*== Delete ==*/
	public function delete(Request $request)
	{
		$mar_id = $request->mar_id;

		// get data
		$info = Marketing::find($mar_id);
		// theme checking
		if(empty($info))
		{
			$response['status'] = "error";
			$response['message'] = "Informasi with ID : $mar_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
			// delete
			Marketing::find($mar_id)->delete();
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, gagal menghapus data penjualan";
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
    
    public function sales_list(){
        $list = Sales::pluck("sales_nama","sales_id")->toArray();
        
		//$response['status'] = "success";
		//$response['message'] = "OK";
		//$response['data'] = $list;

		return response()->json($list);
	}
    
    public function wilayah_list(){
        $list = Wilayah::pluck("wil_nama","wil_id")->toArray();
        
		//$response['status'] = "success";
		//$response['message'] = "OK";
		//$response['data'] = $list;

		return response()->json($list);
	}


}