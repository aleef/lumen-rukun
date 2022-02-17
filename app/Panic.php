<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Panic extends Model
{
    protected $table = 'panic';
    protected $primaryKey = 'panic_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($wil_id='', $keyword='', $warga_id='')
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*', 'c.*')
            ->join('warga as b','a.warga_id','=','b.warga_id')
            ->join('kategori_panic as c','a.kp_id','=','c.kp_id');

         if($warga_id!='')
            $rs = $rs->where('b.warga_id',$warga_id);

         if($wil_id!='')
            $rs = $rs->where('c.wil_id',$wil_id);

         if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                $q->where('c.kp_kategori','ilike',"%$keyword%")
                ->orWhere('b.warga_nama_depan','ilike',"%$keyword%")
                ->orWhere('b.warga_nama_belakang','ilike',"%$keyword%");
            });
         }

         $rs = $rs->orderBy('a.panic_tgl', 'desc');
         $rs = $rs->orderBy('a.panic_jam', 'desc');

         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return [];
     }

     public function get_list_limited($wil_id='', $keyword='', $warga_id='', $page=1, $limit=20)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*', 'c.*')
            ->join('warga as b','a.warga_id','=','b.warga_id')
            ->join('kategori_panic as c','a.kp_id','=','c.kp_id');


         if($warga_id!='')
            $rs = $rs->where('b.warga_id',$warga_id);

         if($wil_id!='')
            $rs = $rs->where('c.wil_id',$wil_id);

         if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                $q->where('c.kp_kategori','ilike',"%$keyword%")
                ->orWhere('b.warga_nama_depan','ilike',"%$keyword%")
                ->orWhere('b.warga_nama_belakang','ilike',"%$keyword%");
            });
         }

         $rs = $rs->orderBy('a.panic_tgl', 'desc');
         $rs = $rs->orderBy('a.panic_jam', 'desc');

         if(!empty($page)) {
            $rs = $rs->limit($limit)->offset(($page-1)*$limit);
        }

         $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return [];
     }

     /* Get Detail */
     public function get_detail($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*', 'c.*')
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->join('kategori_panic as c','c.kp_id','=','a.kp_id')
            ->where($this->primaryKey,$id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

}
