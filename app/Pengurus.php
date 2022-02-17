<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Pengurus extends Model
{
    protected $table = 'pengurus';
    protected $primaryKey = 'pengurus_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($wil_id="", $mk_id="", $keyword="", $mk_status="")
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*','d.*')
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->join('masa_kepengurusan as c','c.mk_id','=','a.mk_id')
            ->join('wilayah as d','d.wil_id','=','c.wil_id')
            ->orderBy('a.updated_at', 'DESC');

         if($wil_id!='')
            $rs = $rs->where('c.wil_id',$wil_id);

         if($mk_id!='')
            $rs = $rs->where('c.mk_id',$mk_id);

         if($mk_status!='')
            $rs = $rs->where('c.mk_status',$mk_status);
        
         if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('b.warga_nama_depan','ilike',"%$keyword%")
                    ->orWhere('b.warga_nama_belakang','ilike',"%$keyword%");
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
            ->select('a.*','b.*','c.*')
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->join('masa_kepengurusan as c','c.mk_id','=','a.mk_id')
            ->where($this->primaryKey,$id)
            ->first();
 
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     public function get_list_with_token($wil_id="")
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*', 'd.fcm_token')
            ->join('warga as b','a.warga_id','=','b.warga_id')
            ->join('masa_kepengurusan as c','a.mk_id','=','c.mk_id')
            ->join('core_user as d','a.warga_id','=','d.user_ref_id');
            
         if($wil_id != '')
            $rs = $rs->where('b.wil_id',$wil_id);

         $rs = $rs->where('c.mk_status',1);
         $rs = $rs->get();
             
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get List Pengurus */
     public function get_list_pengurus($wil_id="")
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*','d.*')
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->join('masa_kepengurusan as c','c.mk_id','=','a.mk_id')
            ->join('core_user as d','d.user_ref_id','=','a.warga_id');

         if($wil_id!='')
            $rs = $rs->where('c.wil_id',$wil_id);

         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";

     }

     /* Get List Admin */
     public function get_list_admin($wil_id="")
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*','d.*')
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->join('masa_kepengurusan as c','c.mk_id','=','a.mk_id')
            ->join('core_user as d','d.user_ref_id','=','a.warga_id')
            ->where([
                    ['c.mk_status', 1],
                    ['d.user_type', 2],
                    ['d.user_status', 1]
                ]);

         if($wil_id!='')
            $rs = $rs->where('c.wil_id',$wil_id);

         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";

     }

     /* Get Count Admin */
     public function get_count_admin($wil_id="")
     {
        $rs = DB::table("$this->table as a")
            ->select(DB::raw('count(b.warga_id) as c_admin'))
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->join('masa_kepengurusan as c','c.mk_id','=','a.mk_id')
            ->join('core_user as d','d.user_ref_id','=','a.warga_id')
            ->where([
                    ['c.mk_status', 1],
                    ['d.user_type', 2],
                    ['d.user_status', 1]
                ]);

         if($wil_id!='')
            $rs = $rs->where('c.wil_id',$wil_id);

         $rs = $rs->first();

         if(!empty($rs))
             return $rs;
         else
             return "";

     }

     /* Get List Pengurus First */
     public function get_list_pengurus_fisrt($wil_id="")
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*','d.*')
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->join('masa_kepengurusan as c','c.mk_id','=','a.mk_id')
            ->join('core_user as d','d.user_ref_id','=','a.warga_id');

         if($wil_id!='')
            $rs = $rs->where('c.wil_id',$wil_id);

        $rs = $rs->first();

         if(!empty($rs))
             return $rs;
         else
             return "";

     }

     /* Get Detail */
     public function get_detail_jabatan($mk_id="", $pengurus_jabatan="")
     {
         $rs = DB::table("$this->table as a")
            ->select(DB::raw('COUNT(a.mk_id) as nama_jabatan'));

         if($mk_id!='')
            $rs = $rs->where('a.mk_id',$mk_id);

         if($pengurus_jabatan!=''){
            $rs = $rs->where('a.pengurus_jabatan', 'LIKE', '%'.$pengurus_jabatan.'%');
         }

         $rs = $rs->first();
 
         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get List Pengurus Active */
     public function get_pengurus_active($wil_id="")
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*','d.*')
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->join('masa_kepengurusan as c','c.mk_id','=','a.mk_id')
            ->join('core_user as d','d.user_ref_id','=','a.warga_id')
            ->where([
                    ['c.mk_status', 1],
                    ['d.user_type', 2],
                    ['d.user_status', 0]
                ]);

         if($wil_id!='')
            $rs = $rs->where('c.wil_id',$wil_id);

         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return "";

     }
}