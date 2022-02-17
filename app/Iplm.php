<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class IPLM extends Model
{
	protected $table = 'ipl_master';
    protected $primaryKey = 'ipl_master_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($wil_id)
     {
         $rs = DB::table("$this->table as a")
             ->select('a.*')
             ->join('wilayah as b','b.wil_id','=','a.wil_id')
             ->where('a.wil_id', $wil_id)
             ->first();
             
         if(!empty($rs))
             return $rs;
         else
             return "";
     }
}