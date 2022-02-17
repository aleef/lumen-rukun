<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Penerimapanic extends Model
{
    protected $table = 'penerima_panic';
    protected $primaryKey = 'pp_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($wil_id='', $kp_id = '', $keyword = '')
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*','d.*')
            ->join('pengurus as b','a.pengurus_id','=','b.pengurus_id')
            ->join('kategori_panic as c','a.kp_id','=','c.kp_id')
            ->join('warga as d','b.warga_id','=','d.warga_id');

         if($wil_id != '')
            $rs = $rs->where('c.wil_id',$wil_id);

         if($kp_id != '')
            $rs = $rs->where('a.kp_id',$kp_id);

         if($keyword != '') {
            $rs = $rs->where(function($q) use ($keyword) {
                $q->where('c.kp_kategori','ilike',"%$keyword%")
                ->orWhere('d.warga_nama_depan','ilike',"%$keyword%")
                ->orWhere('d.warga_nama_belakang','ilike',"%$keyword%");
            });
         }

         $rs = $rs->orderBy('d.warga_nama_depan','asc');

         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return [];
     }


     public function get_pengurus_aktif($wil_id="") {
        $rs = DB::table("pengurus as a")
             ->select('a.pengurus_id', 'a.pengurus_jabatan','a.warga_id', 'b.warga_nama_depan', 'b.warga_nama_belakang',
             'c.mk_status', DB::raw("(b.warga_nama_depan || ' ' || b.warga_nama_belakang) as pengurus_nama"))
             ->join('warga as b', 'a.warga_id','=','b.warga_id')
             ->join('masa_kepengurusan as c', 'a.mk_id','=', 'c.mk_id');

        if($wil_id != '')
            $rs = $rs->where('c.wil_id',$wil_id);
        $rs = $rs->where('c.mk_status',1);

        $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return [];
     }

     public function get_list_kategori($wil_id = '', $pengurus_id = '') {
         $rs = DB::table("kategori_panic as a")
                ->select('a.*');

        if($wil_id != '')
            $rs = $rs->where('a.wil_id', $wil_id);

        if($pengurus_id != '') {
            $rs = $rs->whereRaw("a.kp_id not in (select kp_id from penerima_panic where pengurus_id = ".$pengurus_id.")");
        }

        $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return [];
     }


    /* Get List Pengurus */
     public function get_list_penerima($wil_id="", $kp_id="")
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*','d.*')
            ->join('pengurus as b','b.pengurus_id','=','a.pengurus_id')
            ->join('warga as c','c.warga_id','=','b.warga_id')
            ->join('core_user as d','d.user_ref_id','=','c.warga_id');

        if($wil_id!='')
            $rs = $rs->where('c.wil_id',$wil_id);

        if($kp_id!='')
            $rs = $rs->where('a.kp_id',$kp_id);

        $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";

     }

}
