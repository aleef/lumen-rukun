<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Kprogres extends Model
{
    protected $table = 'progres_komplain';
    protected $primaryKey = 'progres_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($keyword='', $komp_id='')
     {
        
        $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('komplain as b','b.komp_id','=','a.komp_id');

        if($komp_id!='')
            $rs = $rs->where('a.komp_id',$komp_id);

        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.progres_deskirpsi','like',"%$keyword%");
            });  
        }

        $rs = $rs->get();
             
        
        if(!empty($rs))
             return $rs;
        else
             return "";
     }

     /* Get List */
     public function get_list_by_komplain($komp_id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('komplain as b','b.komp_id','=','a.komp_id')
            ->where('a.komp_id',$komp_id)
            ->get();
             
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail */
     public function get_detail($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('komplain as b','b.komp_id','=','a.komp_id')
            ->where($this->primaryKey,$id)
            ->first();
 
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}