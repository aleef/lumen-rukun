<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Dt extends Model
{
    protected $table = 'detil_tagihan';
    protected $primaryKey = 'dt_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($tag_id='')
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*');

         if($tag_id!='')
            $rs = $rs->where('a.tag_id',$tag_id);
             
         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}