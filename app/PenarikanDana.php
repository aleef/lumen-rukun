<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;

class PenarikanDana extends Model
{
    protected $table = 'penarikan_dana';
    protected $primaryKey = 'pd_id';
    public $timestamps = false;
    
    protected $fillable = [
        'pd_tgl',
        'pd_jumlah',
        'pd_ket'
    ];

    /* Get List */
     public function get_list($start, $length, $order, $dir, $search='')
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.wil_nama', 'b.wil_alamat', 'c.sales_nama', 'c.sales_id')
            ->join('wilayah as b','b.wil_id','=','a.wil_id')
            ->join('sales as c','c.sales_id','=','a.sales_id');

       

        if($search!=''){
            $rs = $rs->where(function($q) use ($search) {
                    $q->where('b.wil_nama','ilike',"%$search%")
                    ->orWhere('c.sales_nama','ilike',"%$search%");
            });
        }

		$rs->orderBy('a.mar_id', 'desc');

		if($length != 0) {
			$rs = $rs->limit($length);
		}

        $rs = $rs->get();

         if(!empty($rs)){

            $result = array();
            $i=0;
            foreach($rs as $row){


                $result[$i]['mar_id'] = $row->mar_id;
                $result[$i]['wil_nama'] = $row->wil_nama;
                $result[$i]['wil_alamat'] = $row->wil_alamat;
                $result[$i]['sales_nama'] = $row->sales_nama;
                $result[$i]['mar_mulai_handle'] = $row->mar_mulai_handle;
                $result[$i]['mar_status'] = $row->mar_status;
                $i++;
            }

            return $result;
         }
         else
             return "";
     }

     /* Get Detail */
     public function get_detail($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*', 'c.*')
            ->join('jenis_informasi as b','b.ji_id','=','a.ji_id')
            ->join('wilayah as c','c.wil_id','=','a.wil_id')
            ->where($this->primaryKey,$id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}
