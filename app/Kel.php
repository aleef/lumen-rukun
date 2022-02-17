<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Kel extends Model
{
    protected $table = 'kelurahan';
    protected $primaryKey = 'kel_id';
    public $timestamps = false;

    /* Get List */
    public function get_list($id='', $keyword='')
    {
        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->join('kecamatan as b','b.kec_id','=','a.kec_id')
            ->where('a.kec_id', $id);
        
        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.kel_nama','ilike',"%$keyword%");
            });  
        }

        $rs = $rs->get();

        if(!empty($rs))
             return $rs;
        else
             return "";
    }

     /* Get Kel Nama */
     public function get_kel_nama($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.kel_id', $id)
            ->get();
             
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

    /* Get List */
    public function get_list_($id='', $keyword='')
    {
        $rs = DB::table("$this->table as a")
            ->select('a.*');
        
        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.kel_nama','ilike',"%$keyword%");
            });  
        }

        $rs = $rs->get();

        if(!empty($rs))
             return $rs;
        else
             return "";
    }

}