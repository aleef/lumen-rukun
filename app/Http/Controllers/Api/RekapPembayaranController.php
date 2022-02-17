<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\RekapPembayaran;
use App\Billing;
use App\PaketLangganan;
use App\Wilayah;
use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class RekapPembayaranController extends Controller
{
    
    public function list(Request $request)
	{
			$draw = $request->get('draw');
			$start = $request->get("start");
			$length = $request->get("length");
			$search = $request->get('search')['value'];
	
			$order =  $request->get('order');

			
			$start_m = $request->get('start_m');
			$start_y = $request->get('start_y');
			if($start_m !='' || $start_m){
				$dari = $start_y.'-'.$start_m.'-01';
			}else{
				$dari ='';
			}
			$end_m = $request->get('end_m');
			$end_y = $request->get('end_y');
			if($end_m !='' || $end_m){
				if($end_m=='2'){
					$sampai = $end_y.'-'.$end_m.'-28';
				}elseif($end_m == '1' || $end_m == '3' || $end_m == '5' || $end_m == '7' || $end_m == '8' || $end_m == '10' || $end_m == '12'){
					$sampai = $end_y.'-'.$end_m.'-31';
				}else{
					$sampai = $end_y.'-'.$end_m.'-30';
				}
			}else{
				$sampai = '';
			}
	
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
			 $columns_valid = array("bil_id", "bil_tgl_bayar", "wil_nama", "pl_nama");
			 if(!isset($columns_valid[$col])) {
				$order = null;
			} else {
				$order = $columns_valid[$col];
			}
			$rs = DB::table("wilayah as w")
            ->select('b.bil_tgl_bayar', 'w.wil_nama', 'p.pl_nama', 'b.bil_jumlah', 'b.bil_cara_bayar', 'b.bil_id')
            ->join('billing as b','b.wil_id','=','w.wil_id')
            ->join('paket_langganan as p','p.pl_id','=','b.pl_id');

       

			/*if($search!=''){
				$rs = $rs->where(function($q) use ($search) {
						$q->where('w.wil_nama','ilike',"%$search%")
						->orWhere('p.pl_nama','ilike',"%$search%");
				});
			}*/

			if($dari != ''){
				$rs = $rs->whereRaw("TO_DATE(TO_CHAR(b.bil_tgl_bayar, 'YYYY-MM'), 'YYYY-MM') >= TO_DATE(TO_CHAR(date'".$dari."', 'YYYY-MM'), 'YYYY-MM')")
						->whereRaw("TO_DATE(TO_CHAR(b.bil_tgl_bayar, 'YYYY-MM'), 'YYYY-MM') <= TO_DATE(TO_CHAR(date'".$sampai."', 'YYYY-MM'), 'YYYY-MM')");
			}
			$rs = $rs->where('b.bil_cara_bayar','ilike', '%'.$request->cara_bayar.'%');

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
						$start+$i,
						(Carbon::parse($r->bil_tgl_bayar)->format('d-m-Y') ),
						$r->wil_nama,
						$r->pl_nama,
						number_format($r->bil_jumlah,0,',','.'),
						$r->bil_cara_bayar
					);
					$i++;
				}
				//total data
			   $total_sal = DB::table("wilayah as w")
			   ->join('billing as b','b.wil_id','=','w.wil_id')
			   ->join('paket_langganan as p','p.pl_id','=','b.pl_id');
				if($dari != ''){
					$total_sal = $total_sal->whereRaw("TO_DATE(TO_CHAR(b.bil_tgl_bayar, 'YYYY-MM'), 'YYYY-MM') >= TO_DATE(TO_CHAR(date'".$dari."', 'YYYY-MM'), 'YYYY-MM')")
							->whereRaw("TO_DATE(TO_CHAR(b.bil_tgl_bayar, 'YYYY-MM'), 'YYYY-MM') <= TO_DATE(TO_CHAR(date'".$sampai."', 'YYYY-MM'), 'YYYY-MM')");
				}
				$total_sal = $total_sal->where('b.bil_cara_bayar','ilike', '%'.$request->cara_bayar.'%');  		 
			   	$total_sal =  $total_sal->count();
			   //total filtered
			   $total_fil = DB::table("wilayah as w")
			   ->join('billing as b','b.wil_id','=','w.wil_id')
			   ->join('paket_langganan as p','p.pl_id','=','b.pl_id');
			   if($search!=''){
					$total_fil = $total_fil->where('w.wil_nama','ilike',"%$search%")
					->orWhere('p.pl_nama','ilike',"%$search%");
				}
				
				if($dari != ''){
					$total_fil = $total_fil->whereRaw("TO_DATE(TO_CHAR(b.bil_tgl_bayar, 'YYYY-MM'), 'YYYY-MM') >= TO_DATE(TO_CHAR(date'".$dari."', 'YYYY-MM'), 'YYYY-MM')")
							->whereRaw("TO_DATE(TO_CHAR(b.bil_tgl_bayar, 'YYYY-MM'), 'YYYY-MM') <= TO_DATE(TO_CHAR(date'".$sampai."', 'YYYY-MM'), 'YYYY-MM')");
				}
				$total_fil = $total_fil->where('b.bil_cara_bayar','ilike', '%'.$request->cara_bayar.'%');
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
    public function get_total(Request $request)
	{
			
			$start_m = $request->get('start_m');
			$start_y = $request->get('start_y');
			if($start_m !='' || $start_m){
				$dari = $start_y.'-'.$start_m.'-01';
			}else{
				$dari ='';
			}
			$end_m = $request->get('end_m');
			$end_y = $request->get('end_y');
			if($end_m !='' || $end_m){
				if($end_m=='2'){
					$sampai = $end_y.'-'.$end_m.'-28';
				}elseif($end_m == '1' || $end_m == '3' || $end_m == '5' || $end_m == '7' || $end_m == '8' || $end_m == '10' || $end_m == '12'){
					$sampai = $end_y.'-'.$end_m.'-31';
				}else{
					$sampai = $end_y.'-'.$end_m.'-30';
				}
			}else{
				$sampai = '';
			}
	
			$rs = DB::table("wilayah as w")
						->select(DB::raw('SUM(b.bil_jumlah) AS total'))
						->join('billing as b','b.wil_id','=','w.wil_id')
						->join('paket_langganan as p','p.pl_id','=','b.pl_id');       

			if($dari != ''){
				$rs = $rs->whereRaw("TO_DATE(TO_CHAR(b.bil_tgl_bayar, 'YYYY-MM'), 'YYYY-MM') >= TO_DATE(TO_CHAR(date'".$dari."', 'YYYY-MM'), 'YYYY-MM')")
						->whereRaw("TO_DATE(TO_CHAR(b.bil_tgl_bayar, 'YYYY-MM'), 'YYYY-MM') <= TO_DATE(TO_CHAR(date'".$sampai."', 'YYYY-MM'), 'YYYY-MM')");
			}
			$rs = $rs->where('b.bil_cara_bayar','ilike', '%'.$request->cara_bayar.'%');


			$rs = $rs->first();
			
			$response['status'] = "success";
			$response['total'] = number_format($rs->total,0,',','.');
 
			 // return json response
			 return response()->json($response, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
		
        
    }
    
    
	/*==  Detail ==*/
	public function detail($id, Request $request, RekapPembayaran $mar)
	{
		// get data
		$info = RekapPembayaran::find($id);
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
        $mar = new RekapPembayaran;
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


		$mar = RekapPembayaran::find($mar_id);
        
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
		$info = RekapPembayaran::find($mar_id);
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
			RekapPembayaran::find($mar_id)->delete();
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