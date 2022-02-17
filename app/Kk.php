<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Kk extends Model
{
    protected $table = 'komen_komplain';
    protected $primaryKey = 'kk_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($kk_warga_id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('warga as b','b.warga_id','=','a.kk_warga_id')
            ->where('a.kk_warga_id',$kk_warga_id)
            ->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get List */
     public function get_list_by_komplain($komp_id)
     {
         $rs = DB::table("$this->table as a")
            ->select(['a.*',\DB::raw("to_char(a.create_date, 'DD Mon YYYY HH:MM:SS') as simplify_create_date"), 'b.*'])
            ->join('warga as b','b.warga_id','=','a.kk_warga_id')
            ->where('a.komp_id',$komp_id)
            ->orderBy('kk_id','desc')
            ->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     public function get_list_komentator_token($komp_id, $warga_id) {
         $rs = DB::select('select a.komp_id, a.kk_warga_id as warga_id,
                                b.warga_nama_depan, c.fcm_token
                                from komen_komplain a
                                inner join warga b on a.kk_warga_id = b.warga_id
                                inner join core_user c on b.warga_id = c.user_ref_id
                                where a.komp_id = ?
                                and a.kk_warga_id != ?
                                union
                                select a.komp_id, a.warga_id, b.warga_nama_depan, c.fcm_token
                                from komplain a
                                inner join warga b on a.warga_id = b.warga_id
                                inner join core_user c on b.warga_id = c.user_ref_id
                                where a.komp_id = ?
                                and a.warga_id != ?',[$komp_id, $warga_id, $komp_id, $warga_id]);


        if(!empty($rs)) {
            return $rs;
        }else {
            return "";
        }


     }

     /* Get Detail */
     public function get_detail($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('warga as b','b.warga_id','=','a.kk_warga_id')
            ->where($this->primaryKey,$id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}
