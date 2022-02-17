<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Sales;
//use DataTables;
use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SalesController extends Controller
{
    
    public function list(Request $request, Sales $sales)
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
         $columns_valid = array("sales_nama");
         if(!isset($columns_valid[$col])) {
            $order = null;
        } else {
            $order = $columns_valid[$col];
        }
        //$info = $sales->get_list($start, $length, $order, $dir, $search);
        $res =  Sales::where('sales.sales_head', '1');
        if($search!=''){
            $res = $res->where('sales.sales_nama','ilike',"%$search%");
        }
        $res = $res->get();
        $i = 1;
        //$data[] =array();
         foreach($res as $r) {
             
             $data[] = array(
                 '<a class="details-control"><i class="fa fa-chevron-down"></i></a>'.$i,
                 '<input type="hidden" class="lead_id" value="'.$r->sales_id.'">'.$r->sales_nama,
                 $r->sales_kode,
                 $r->sales_hp,
                 $r->sales_email,
                 '<form action="sales/'.$r->sales_id.'/destroy" method="POST"><a href="sales/'.$r->sales_id.'/editLead"><i class="fa fa-edit fa-lg text-success" title="Edit"></i></a> <a href="#" id="hapusdata-id="'.$r->sales_id.'" data-nama="'.$r->sales_nama.'"><i class="fa fa-trash fa-lg text-danger" title="Hapus"></i></a></form>'
             );
             $i++;
         }

         //total data lead
        $total_sal = Sales::where('sales.sales_head', '1')->count();
        //total filtered
        $total_fil = Sales::where('sales.sales_head', '1');
        if($search!=''){
             $total_fil = $res->where('sales.sales_nama','ilike',"%$search%");
         }
         $total_fil = $total_fil->count();

         $output = array(
             "draw" => $draw,
             "recordsTotal" => $total_sal,
             "recordsFiltered" => $total_fil,
             "data" => $data
         );
        /*if(empty($info) || $info ='')
        {
            $response['status'] = "error";
            $response['message'] = "Sales not found";
            return response()->json($response);
            exit();
        }*/

        
        /*$result = Sales::where('sales.sales_head', '1')->latest()->get();
         $response['status'] = "success";
         $response['message'] = "OK";
         $response['results'] = $result;*/
 
         // return json response
         return response()->json($output, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        
    }
    
    
	/*==  Detail ==*/
	public function detail($id, Request $request, Sales $sales)
	{
		// get data
		$info = Sales::find($id);
		/*if(empty($info))  
		{
			$response['status'] = "error";
			$response['message'] = "Informasi not found";
            $response['results'] = [];
			return response()->json($response);
			exit();
		}

	
		$results = array(
			"sales_nama" => $sales->sales_nama,
			"sales_hp" => $sales->sales_hp,
			"sales_email" => $sales->sales_email,
			"sales_kode" => $sales->sales_kode,
			"sales_parent_id" => $sales->sales_parent_id,
			"sales_head" => $sales->sales_head,
		);

		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $results;

		return response()->json($response);*/
        return $info;
	}

	/*== Add ==*/
	public function add(Request $request)
	{
        try{
            
            $sales = new Sales;
            $sales->sales_nama = $request->sales_nama;
            $sales->sales_hp = $request->sales_hp;
            $sales->sales_email = $request->sales_email;
            $sales->sales_head = $request->sales_head;
            $sales->sales_parent_id = $request->sales_parent_id;
            $sales->sales_kode = $request->sales_kode;
            
            $sales->save();
            
            // response
            $response['status'] = "success";
            $response['message'] = "OK";
            $response['results'] = "Sukses menyimpan sales baru";
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
		$sales_id = $request->sales_id;


		$sales = Sales::find($sales_id);

        $sales->sales_nama = $request->sales_nama;
        $sales->sales_hp = $request->sales_hp;
        $sales->sales_email = $request->sales_email;
        $sales->sales_head = $request->sales_head;
        $sales->sales_parent_id = $request->sales_parent_id;
        $sales->sales_kode = $request->sales_kode;
        
        $sales->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses mengubah data sales ".$request->sales_nama;

		// return json response
		return response()->json($response);
	}
	public function updateLead(Request $request)
	{
		// account warga
		$sales_id = $request->sales_id;


		$sales = Sales::find($sales_id);

        $sales->sales_nama = $request->sales_nama;
        $sales->sales_hp = $request->sales_hp;
        $sales->sales_email = $request->sales_email;
        $sales->sales_kode = $request->sales_kode;
        
        $sales->save();

		// response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = "Sukses mengubah data lead sales ".$request->sales_nama;

		// return json response
		return response()->json($response);
	}

	/*== Delete ==*/
	public function delete(Request $request)
	{
		$sales_id = $request->sales_id;

		// get data
		$info = Sales::find($sales_id);
		// theme checking
		if(empty($info))
		{
			$response['status'] = "error";
			$response['message'] = "Informasi with ID : $sales_id not found";
			return response()->json($response);
			exit();
		}

		try
		{
			// delete
			Sales::find($sales_id)->delete();
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the Informasi";
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
    
    public function team(Request $request){
		$id_lead	= $request->id_lead;
        //$list 		= Sales::select('sales.*')
        $list = DB::table('sales')
                        ->where('sales.sales_parent_id', $id_lead)
                        ->get();
        //echo $this->db->last_query();
        echo(json_encode($list));
	}
    public function leadlist(){

		$list = Sales::where('sales.sales_head', '1')->pluck("sales_nama","sales_id")->toArray();
        
		//$response['status'] = "success";
		//$response['message'] = "OK";
		//$response['data'] = $list;

		return response()->json($list);
	}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $sales = Sales::find($id);
        $leadlist =DB::table('sales')->where('sales.sales_lead', '1')->get();
        return view('sales.edit',compact('sales','leadlist'));
    }

}