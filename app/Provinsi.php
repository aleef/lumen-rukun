<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Provinsi extends Model
{
    protected $table = 'propinsi';
    protected $primaryKey = 'prop_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($keyword='')
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
     /* Get List Kab from Kec*/
     public function get_prov($id='', $keyword='')
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->join('kabkota as b','b.prop_id','=','a.prop_id')
            ->join('kecamatan as c','c.kabkota_id','=','b.kabkota_id')
            ->join('kelurahan as d','c.kec_id','=','d.kec_id')
            ->where('d.kel_id', $id);


        $rs = $rs->get();
             
        if(!empty($rs))
             return $rs;
        else
             return "";
     }

}