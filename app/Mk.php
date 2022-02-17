<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Mk extends Model
{
    protected $table = 'masa_kepengurusan';
    protected $primaryKey = 'mk_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.wil_id', $id)
            ->get();
             
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail */
     public function get_detail($id)
     {
         $rs = DB::table($this->table)
            ->select('wil_nama', 'mk_periode_mulai', 'mk_periode_akhir', 'mk_status', 'mk_sk')
            ->where($this->primaryKey,$id)
            ->first();
 
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Active */
     public function get_active($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.wil_id', $id)
            ->where('mk_status', 1)
            ->first();
 
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}