<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Jti extends Model
{
    protected $table = 'jenis_tagihan_insidental';
    protected $primaryKey = 'jti_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($wil_id='')
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*');

         if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);
             
         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}