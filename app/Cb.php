<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Cb extends Model
{
    protected $table = 'ipl_cara_bayar';
    protected $primaryKey = 'ipl_cara_bayar_id';
    public $timestamps = false;

    /* Get List */
     public function get_list()
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->get();
             
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}