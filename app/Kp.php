<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Kp extends Model
{
    protected $table = 'kategori_panic';
    protected $primaryKey = 'kp_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($wil_id='', $keyword='')
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*');


         if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

         if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.kp_kategori','ilike',"%$keyword%");
            });
         }

         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return [];
     }

     public function get_list_button($wil_id='', $keyword='')
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->whereRaw("a.kp_id in (select kp_id from penerima_panic)");

         if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

         if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.kp_kategori','ilike',"%$keyword%");
            });
         }

         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return [];
     }

}
