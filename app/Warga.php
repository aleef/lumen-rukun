<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Warga extends Model
{
    protected $table = 'warga';
    protected $primaryKey = 'warga_id';
    public $timestamps = false;


     /* Get List */
     public function get_list($wil_id="", $keyword="", $warga_id="")
     {
        $rs = DB::table("$this->table as a")
             ->select('a.*')
             ->join('wilayah as b','b.wil_id','=','a.wil_id');

        if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

        if($warga_id!='')
            $rs = $rs->where('a.warga_id', '!=' ,$warga_id);

        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.warga_nama_depan','ilike',"%$keyword%")
                    ->orWhere('a.warga_nama_belakang','ilike',"%$keyword%");
            });
        }
        $rs = $rs->orderBy('a.warga_nama_depan','asc');

        $rs = $rs->get();

        if(!empty($rs))
             return $rs;
        else
             return "";
     }

     public function get_list_limited($wil_id="", $keyword="", $warga_id="", $page=1, $limit=20)
     {
        $rs = DB::table("$this->table as a")
             ->select('a.*')
             ->join('wilayah as b','b.wil_id','=','a.wil_id');

        if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

        if($warga_id!='')
            $rs = $rs->where('a.warga_id', '!=' ,$warga_id);

        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.warga_nama_depan','ilike',"%$keyword%")
                    ->orWhere('a.warga_nama_belakang','ilike',"%$keyword%");
            });
        }
        $rs = $rs->orderBy('a.warga_nama_depan','asc');
        if(!empty($page)) {
            $rs = $rs->limit($limit)->offset(($page-1)*$limit);
        }

        $rs = $rs->get();

        if(!empty($rs))
             return $rs;
        else
             return "";
     }

     public function get_list_unregistered($wil_id="", $keyword="")
     {
        $rs = DB::table("warga_undang as a")
             ->select('a.*');

        if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.undang_email','ilike',"%$keyword%");
            });
        }

        $rs->where('a.status','=','0');
        $rs->orderBy('a.undang_email','asc');

        $rs = $rs->get();

        if(!empty($rs))
             return $rs;
        else
             return "";
     }

     /* Get List */
     public function get_warga_not_pengurus($wil_id, $mk_id)
     {
        //get warga ID pengurus
        $rs = DB::table("pengurus as a")
             ->select('a.warga_id')
             ->leftJoin('masa_kepengurusan as b','b.mk_id','=','a.mk_id')
             ->where('b.wil_id', $wil_id)
             ->where('b.mk_id', $mk_id)
             ->get();

             $warga = array();

             foreach ($rs as $rows) {

                $warga_id = $rows->warga_id;

                $warga[] = $warga_id;

             }

             $rss = DB::table("$this->table as a")
                     ->select('a.*')
                     ->join('wilayah as b','b.wil_id','=','a.wil_id')
                     ->where('a.wil_id', $wil_id)
                     ->whereNotIn('warga_id', $warga)
                     ->get();

            //print_r($rss);

                 if(!empty($rss))
                     return $rss;
                 else
                     return "null";


     }

     /* Get Detail */
     public function get_detail($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.fcm_token','c.user_email','d.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id')
            ->join('core_user as c','c.user_ref_id','=','a.warga_id')
            ->leftJoin('kategori_bangunan as d','d.kb_id','=','a.kb_id')
            ->where($this->primaryKey,$id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail by Email */
     public function get_detail_email($email)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.warga_email',$email)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail by Email */
     public function get_detail_email_mk($email)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('masa_kepengurusan as b','b.wil_id','=','a.wil_id')
            ->where('a.warga_email',$email)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail SignIn*/
     public function get_detail_signin($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id')
            ->join('core_user as c','c.user_ref_id','=','a.warga_id')
            ->where($this->primaryKey,$id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }


    /* Get Warga by Wil */
     public function get_list_warga($wil_id)
     {
         $rs = DB::table("$this->table as a")
             ->select('a.warga_id')
             ->where('a.wil_id', $wil_id)
             ->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

	 public function get_warga_not_pengurus_with_token($wil_id) {
		$rs = DB::select('select a.*, d.fcm_token
							from warga as a
							inner join core_user as d on a.warga_id = d.user_ref_id
							where wil_id = ?
							and warga_id not in (
							select a.warga_id
							from pengurus as a
							left join masa_kepengurusan as b on a.mk_id = b.mk_id
							where b.mk_status = 1
							)',[$wil_id]);


        if(!empty($rs)) {
            return $rs;
        }else {
            return "";
        }
	 }

     public function get_pengurus_with_token($wil_id) {
		$rs = DB::select('select a.*, d.fcm_token
							from warga as a
							inner join core_user as d on a.warga_id = d.user_ref_id
							where wil_id = ?
							and warga_id in (
							select a.warga_id
							from pengurus as a
							left join masa_kepengurusan as b on a.mk_id = b.mk_id
							where b.mk_status = 1
							)',[$wil_id]);


        if(count($rs) > 0) {
            return $rs;
        }else {
            return [];
        }
	 }

     /* Get by email*/
     public function get_detail_by_email($email)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.warga_email',$email)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     public function get_all_warga_except_id($wil_id, $warga_id = 0) {
		$rs = DB::select('select a.*, d.fcm_token
							from warga as a
							inner join core_user as d on a.warga_id = d.user_ref_id
							where a.wil_id = ?
							and a.warga_id not in (?)',[$wil_id, $warga_id]);


        if(!empty($rs)) {
            return $rs;
        }else {
            return "";
        }
	 }

     /* Get HP */
     public function get_hp($hp)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.warga_hp',$hp)
            ->first();

         if(!empty($rs))
             return $rs;
         else
            return "";
     }

     /* Get Warga by Wil */
     public function get_pengurus($wil_id)
     {
         $rs = DB::table("$this->table as a")
             ->select('a.*','b.*')
             ->join('core_user as b','b.user_ref_id','=','a.warga_id')
             ->where([
                ['a.wil_id', $wil_id],
                ['b.user_type', 2],
                ['b.user_status', 0]
             ])
             ->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Warga by Wil */
     public function get_pengurus_nonaktif($wil_id)
     {
         $rs = DB::table("$this->table as a")
             ->select('a.*','b.*')
             ->join('core_user as b','b.user_ref_id','=','a.warga_id')
             ->where([
                ['a.wil_id', $wil_id],
                ['b.user_type', 3],
                ['b.user_status', 0]
             ])
             ->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Warga by Wil */
     public function get_pengurus_by_warga($warga_id)
     {
         $rs = DB::table("$this->table as a")
             ->select('a.*','b.*')
             ->join('core_user as b','b.user_ref_id','=','a.warga_id')
             ->where([
                ['a.warga_id', $warga_id],
                ['b.user_type', 2],
                ['b.user_status', 0]
             ])
             ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }


}
