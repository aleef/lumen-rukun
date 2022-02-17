<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Percakapan extends Model
{
    protected $table = 'percakapan';
    protected $primaryKey = 'percakapan_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($wil_id='', $keyword='', $warga_id='')
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('warga as b','b.warga_id','=','a.second_warga_id')
            ->orderBy('a.percakapan_id', 'desc');

         if($warga_id!='')
            $rs = $rs->where('b.warga_id',$warga_id);

         if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);
        
         if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('b.warga_nama_depan','ilike',"%$keyword%")
                    ->orWhere('b.warga_nama_belakang','ilike',"%$keyword%");
            });  
         }

         $rs = $rs->get();
             
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail */
     public function get_detail($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where($this->primaryKey,$id)
            ->first();
 
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail */
     public function get_detail_by_warga($identity="")
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*');
         
         if($identity!='')
            $rs = $rs->where('a.identity',$identity);
         
         $rs = $rs->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}