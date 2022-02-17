<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Kec extends Model
{
    protected $table = 'kecamatan';
    protected $primaryKey = 'kec_id';
    public $timestamps = false;

    /* Get List */
    public function get_list($id='', $keyword='')
    {
        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->join('kabkota as b','b.kabkota_id','=','a.kabkota_id')
            ->where('a.kabkota_id', $id);

        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.kec_nama','ilike',"%$keyword%");
            });  
        }

        $rs = $rs->get();
             
        if(!empty($rs))
             return $rs;
        else
             return "";
    }

    /* Get List Kec from Kel*/
    public function get_kec_list($id='', $keyword='')
    {
        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->join('kelurahan as b','b.kec_id','=','a.kec_id')
            ->where('b.kel_id', $id);
        
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
                    $q->where('a.kec_nama','ilike',"%$keyword%");
            });  
        }

        $rs = $rs->get();
             
        if(!empty($rs))
             return $rs;
        else
             return "";
    }

}