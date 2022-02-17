<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Kb extends Model
{
    protected $table = 'kategori_bangunan';
    protected $primaryKey = 'kb_id';
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
                    $q->where('a.kb_keterangan','ilike',"%$keyword%")
                    ->orWhere('a.kb_tarif_ipl','ilike',"%$keyword%");
            });
          }

        $rs = $rs->orderBy('a.kb_id','asc');

         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

    /* Get by Warga ID */
     public function get_kb_nominal($warga_id='')
     {

         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->join('warga as b','b.kb_id','=','a.kb_id')
            ->where('b.warga_id',$warga_id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get by kode wilayah */
     public function get_kb_wil($wil_kode='')
     {

         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id')
            ->where('b.wil_kode',$wil_kode)
            ->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     

}
