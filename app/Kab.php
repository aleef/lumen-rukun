<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Kab extends Model
{
    protected $table = 'kabkota';
    protected $primaryKey = 'kabkota_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($id='', $keyword='')
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.prop_id', $id);

        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.kabkota_nama','ilike',"%$keyword%");
            });
        }

        $rs = $rs->get();

        if(!empty($rs))
             return $rs;
        else
             return "";
     }

    /* Get List Kab from Kec*/
     public function get_kab($id='', $keyword='')
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->join('kecamatan as b','b.kabkota_id','=','a.kabkota_id')
            ->join('kelurahan as c','b.kec_id','=','c.kec_id')
            ->where('c.kel_id', $id);


        $rs = $rs->get();

        if(!empty($rs))
             return $rs;
        else
             return "";
     }

     /* Get List */
     public function get_list_($id='', $keyword='')
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*');

        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.prop_nama','ilike',"%$keyword%");
            });
        }

        $rs = $rs->get();

        if(!empty($rs))
             return $rs;
        else
             return "";
     }

}
