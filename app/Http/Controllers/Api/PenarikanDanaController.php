<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\PenarikanDana;
use App\RekapPembayaran;
use App\Billing;
use App\PaketLangganan;
use App\Wilayah;
use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class PenarikanDanaController extends Controller
{
    
    public function list(Request $request)
	{
			$draw = $request->get('draw');
			$start = $request->get("start");
			$length = $request->get("length");
			$search = $request->get('search')['value'];
	
			$order =  $request->get('order');

			
			$start_date = $request->get('start_date');
			$end_date = $request->get('end_date');

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
			 $columns_valid = array("pd_id", "pd_tgl", "pd_jumlah", "pd_ket");
			 if(!isset($columns_valid[$col])) {
				$order = null;
			} else {
				$order = $columns_valid[$col];
			}
			$rs = DB::table("penarikan_dana as p")
            ->select('p.*');

			if($start_date!= '' && $end_date !=''){
				$rs = $rs->whereRaw("p.pd_tgl >= '".$start."'")
						->whereRaw("p.pd_tgl <= '".$end_date."'");
			}elseif($start_date!= '' && $end_date == ''){
				$rs = $rs->whereRaw("p.pd_tgl >= '" . $start_date. "'");
			}elseif($start_date== '' && $end_date != ''){
				$rs = $rs->whereRaw("p.pd_tgl <= '" . $end_date . "'");
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
						$start+$i,
						(Carbon::parse($r->pd_tgl)->format('d-m-Y') ),
						number_format($r->pd_jumlah, 0, ',', '.'),
						$r->pd_ket,
						'<a href="#" onclick="showEdit('.$r->pd_id. ')" data-toggle="modal" data-id="' . $r->pd_id . '"><i class="fa fa-edit fa-lg text-success"></i></a> <a href="#" id="hapus" data-id="' . $r->pd_id . '" data-tgl="' . $r->pd_tgl . '"><i class="fa fa-trash fa-lg text-danger"></i></a>'
					);
					$i++;
				}
				//total data
			   $total_sal = DB::table("penarikan_dana as p")->select('p.*');

			   	$total_sal =  $total_sal->count();
			   //total filtered
			   $total_fil = DB::table("penarikan_dana as p");
			   /*if($search!=''){
					$total_fil = $total_fil->where('w.wil_nama','ilike',"%$search%")
					->orWhere('p.pl_nama','ilike',"%$search%");
				}*/

				if ($start_date!= '' && $end_date != '') {
					$total_fil = $total_fil->whereRaw("p.pd_tgl >= '" . $start_date. "'")
						->whereRaw("p.pd_tgl <= '" . $end_date . "'");
				} elseif ($start_date!= '' && $end_date == '') {
					$total_fil = $total_fil->whereRaw("p.pd_tgl >= '" . $start_date. "'");
				} elseif ($start_date== '' && $end_date != '') {
					$total_fil = $total_fil->whereRaw("p.pd_tgl <= '" . $end_date . "'");
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
			 return response()->json($output, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
		
        
    }
    public function get_total(Request $request)
	{


			$start_date = $request->get('start_date');
			$end_date = $request->get('end_date');

			if ($start_date != '' && $end_date != '' && $start_date && $end_date) {
				$bildate = "WHERE b.bil_tgl_bayar >= '" . $start_date . "' AND b.bil_tgl_bayar <= '" . $end_date . "'";
			} elseif ($start_date != '' && $end_date == '') {
				$bildate = "WHERE b.bil_tgl_bayar >= '" . $start_date . "'";
			} elseif ($start_date == '' && $end_date != '') {
				$bildate =  "WHERE b.bil_tgl_bayar <= '" . $end_date . "'";
			}else{
				$bildate = '';
			}
		
			$rs = DB::table("penarikan_dana as p")
			->select(DB::raw('SUM(p.pd_jumlah) AS total'), DB::raw("(SELECT SUM(b.bil_jumlah) FROM billing AS b " .$bildate.")-SUM(p.pd_jumlah) as saldo"));

			if ($start_date!= '' && $end_date != '') {
				$rs = $rs->whereRaw("p.pd_tgl >= '" . $start_date. "'")
				->whereRaw("p.pd_tgl <= '" . $end_date . "'");
			} elseif ($start_date!= '' && $end_date == '') {
				$rs = $rs->whereRaw("p.pd_tgl >= '" . $start_date. "'");
			} elseif ($start_date== '' && $end_date != '') {
				$rs = $rs->whereRaw("p.pd_tgl <= '" . $end_date . "'");
			}
			$rs = $rs->first();
			
			$response['status'] = "success";
			$response['total'] = number_format($rs->total,0,',','.');
			$response['saldo'] = number_format($rs->saldo,0,',','.');
 
			 // return json response
			 return response()->json($response, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
		
        
    }
    public function get_total_rekap(Request $request)
	{


		$start_date= $request->get('start_date');
		$end_date = $request->get('end_date');

		$rs = DB::table("wilayah as w")
		->select(DB::raw('SUM(b.bil_jumlah) AS total_rekap'))
		->join('billing as b', 'b.wil_id', '=', 'w.wil_id')
		->join('paket_langganan as p', 'p.pl_id', '=', 'b.pl_id');

		if ($start_date!= '' && $end_date != '') {
			$rs = $rs->whereRaw("b.bil_tgl_bayar >= '" . $start_date. "'")
			->whereRaw("b.bil_tgl_bayar <= '" . $end_date . "'");
		} elseif ($start_date!= '' && $end_date == '') {
			$rs = $rs->whereRaw("b.bil_tgl_bayar >= '" . $start_date. "'");
		} elseif ($start_date== '' && $end_date != '') {
			$rs = $rs->whereRaw("b.bil_tgl_bayar <= '" . $end_date . "'");
		}


		$rs = $rs->first();

		$response['status'] = "success";
		$response['total'] = number_format($rs->total_rekap, 0, ',', '.');

		// return json response
		return response()->json($response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		
        
    }
    
    
	/*==  Detail ==*/
	public function detail($id, Request $request, PenarikanDana $pen)
	{
		// get data
		$info = PenarikanDana::find($id);
		
		return $info;
	}

	/*== Add ==*/
	public function add(Request $request)
	{
        $pen = new PenarikanDana();
        $pen->pd_tgl = $request->pd_tgl;
		$pen->pd_jumlah = str_replace(",", ".", str_replace(".", "", $request->pd_jumlah));
        $pen->pd_ket = $request->pd_ket;
        
        $pen->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses menyimpan data penarikan dana baru";

		// return json response
		return response()->json($response);
	}
	

	/*== Update ==*/
	public function update(Request $request)
	{
		$pen = PenarikanDana::find($request->edit_id);

		$pen->pd_tgl = $request->edit_tgl;
		$pen->pd_jumlah = str_replace(",", ".", str_replace(".", "", $request->edit_jumlah));
		$pen->pd_ket = $request->edit_ket;
        
        $pen->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses mengubah data penarikan ".$request->edit_tgl;

		// return json response
		return response()->json($response);
	}
	
	/*== Delete ==*/
	public function delete(Request $request)
	{
		$pen_id = $request->pd_id;

		// get data
		$info = PenarikanDana::find($pen_id);
		// theme checking
		if(empty($info))
		{
			$response['status'] = "error";
			$response['message'] = "Informasi with ID : $pen_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
			// delete
			PenarikanDana::find($pen_id)->delete();
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
    


}