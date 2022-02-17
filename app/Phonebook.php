<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Phonebook extends Model
{
    protected $table = 'phonebook';
    protected $primaryKey = 'pb_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($wil_id='', $keyword='')
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id');

         if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

         if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.pb_nama','ilike',"%$keyword%")
                    ->orWhere('a.pb_nomor','ilike',"%$keyword%");
            });  
         }

         $rs = $rs->get();
             
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail */
     public function get_detail($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id')
            ->where($this->primaryKey,$id)
            ->first();
 
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}