<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Jp extends Model
{
    protected $table = 'jenis_peraturan';
    protected $primaryKey = 'jp_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($keyword = '')
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*');

        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.jp_jenis','ilike',"%$keyword%");
            });  
        }

        $rs = $rs->get();
             
        if(!empty($rs))
             return $rs;
        else
             return "";
     }

}