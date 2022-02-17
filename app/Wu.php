<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Wu extends Model
{
    protected $table = 'warga_undang';
    protected $primaryKey = 'undang_id';
    public $timestamps = false;

    /* Get Detail */
     public function get_detail($warga_undang_id, $wil_id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*','b.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id')
            ->where($this->primaryKey,$warga_undang_id)
            ->where('a.wil_id',$wil_id)
            ->first();
 
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}