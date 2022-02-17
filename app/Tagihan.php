<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Tagihan extends Model
{
    protected $table = 'tagihan';
    protected $primaryKey = 'tag_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($keyword='', $wil_id='', $bulan='', $tahun='', $tag_status='')
     {

         $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*')
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->join('periode_tagihan as c','c.pt_id','=','a.pt_id');

         if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

         if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('b.warga_nama','ilike',"%$keyword%");
            });  
         }

         if($bulan!='')
            $rs = $rs->where('c.pt_bulan', $bulan);

         if($tahun!='')
            $rs = $rs->where('c.pt_tahun', $tahun);

         if($tag_status!='')
            $rs = $rs->where('a.tag_status', $tag_status);
             
         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get List */
     public function get_list_count($keyword='', $wil_id='', $bulan='', $tahun='')
     {

         $rs = DB::table("$this->table as a")
            ->selectRaw(DB::raw('COUNT(a.tag_total) as tag_count'))
            ->leftJoin('warga as b','b.warga_id','=','a.warga_id')
            ->leftJoin('periode_tagihan as c','c.pt_id','=','a.pt_id')
            ->where('a.tag_total', 0)
            ->groupBy('c.pt_id');

         if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

         if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('b.warga_nama','ilike',"%$keyword%");
            });  
         }

         if($bulan!='')
            $rs = $rs->where('c.pt_bulan', $bulan);

         if($tahun!='')
            $rs = $rs->where('c.pt_tahun', $tahun);
             
         $rs = $rs->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get List Periode */
     public function get_list_periode($wil_id='', $bulan='', $tahun='')
     {

         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->join('periode_tagihan as c','c.pt_id','=','a.pt_id');

         if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

         if($bulan!='')
            $rs = $rs->where('c.pt_bulan', $bulan);

         if($tahun!='')
            $rs = $rs->where('c.pt_tahun', $tahun);
             
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
            ->select('a.*', 'b.*', 'c.*', 'd.*')
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->join('periode_tagihan as c','c.pt_id','=','a.pt_id')
            ->join('kategori_bangunan as d','d.kb_id','=','b.kb_id')
            ->where($this->primaryKey,$id)
            ->first();
 
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail Tagihan JT */
     public function get_detail_jt($tag_id='')
     {
         $rs = DB::table("detil_tagihan as a")
            ->select('a.*', 'b.*')
            ->join('jenis_tagihan as b','b.jt_id','=','a.jt_id');

         if($tag_id!='')
            $rs = $rs->where('a.tag_id',$tag_id);
             
         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail Tagihan JTI */
     public function get_detail_jti($tag_id='')
     {
         $rs = DB::table("detil_tagihan as a")
            ->select('a.*', 'b.*')
            ->join('jenis_tagihan_insidental as b','b.jti_id','=','a.jti_id');

         if($tag_id!='')
            $rs = $rs->where('a.tag_id',$tag_id);
             
         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}