<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Pesan extends Model
{
    protected $table = 'pesan';
    protected $primaryKey = 'pesan_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($percakapan_id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->orderBy('a.time_at', 'desc');

         if($percakapan_id!='')
            $rs = $rs->where('a.percakapan_id',$percakapan_id);

         $rs = $rs->get();
             
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail Pesan by Percakapan */
     public function get_detail_pesan($pesan_id="", $percakapan_id="")
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*');
         
         if($percakapan_id!='')
            $rs = $rs->where('a.percakapan_id',$percakapan_id);

         if($pesan_id!='')
            $rs = $rs->where('a.pesan_id',$pesan_id);
         
         $rs = $rs->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail Pesan by Percakapan */
     public function get_list_pesan_by_percakapan($percakapan_id="")
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.pesan_read', '1');
         
         if($percakapan_id!='')
            $rs = $rs->where('a.percakapan_id',$percakapan_id);
         
         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }
}