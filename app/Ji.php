<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Ji extends Model
{
    protected $table = 'jenis_informasi';
    protected $primaryKey = 'ji_id';
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