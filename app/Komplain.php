<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Komplain extends Model
{
    protected $table = 'komplain';
    protected $primaryKey = 'komp_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($warga_id='', $wil_id='', $status='', $keyword='')
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', DB::raw("(case
                                        when a.komp_status = '0' then 'Belum Ditanggapi'
                                        when a.komp_status = '1' then 'Sedang Diproses'
                                        when a.komp_status = '2' then 'Selesai'
                                        else 'Unknown'
                                      end
                                    ) as status_komplain"),
                                    'b.warga_nama_depan',
                                    'b.warga_nama_belakang',
                                    'b.warga_hp')
            ->join('warga as b','b.warga_id','=','a.warga_id');

        if($status!='')
            $rs = $rs->where('a.komp_status_pp', $status);

        if($warga_id!='')
            $rs = $rs->where('a.warga_id', $warga_id);

        if($wil_id!='')
            $rs = $rs->where('a.wil_id', $wil_id);

		if($keyword != '') {
			$rs = $rs->where('a.komp_judul','ilike','%'.$keyword.'%');
		}

		$rs = $rs->orderBy('a.komp_status', 'asc');
        $rs = $rs->orderBy('a.create_date', 'desc');

        $rs = $rs->get();

         if(!empty($rs))
             return $rs;
         else
             return [];
     }


     public function get_list_limited($warga_id='', $wil_id='', $status='', $keyword='',$page=1, $limit=20)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', DB::raw("(case
                                        when a.komp_status = '0' then 'Belum Ditanggapi'
                                        when a.komp_status = '1' then 'Sedang Diproses'
                                        when a.komp_status = '2' then 'Selesai'
                                        else 'Unknown'
                                      end
                                    ) as status_komplain"),
                                    'b.warga_nama_depan',
                                    'b.warga_nama_belakang',
                                    'b.warga_hp')
            ->join('warga as b','b.warga_id','=','a.warga_id');

        if($status!='')
            $rs = $rs->where('a.komp_status_pp', $status);

        if($warga_id!='')
            $rs = $rs->where('a.warga_id', $warga_id);

        if($wil_id!='')
            $rs = $rs->where('a.wil_id', $wil_id);

		if($keyword != '') {
			$rs = $rs->where('a.komp_judul','ilike','%'.$keyword.'%');
		}

		$rs = $rs->orderBy('a.komp_status', 'asc');
        $rs = $rs->orderBy('a.create_date', 'desc');

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
            ->select('a.*', 'b.*')
            ->join('warga as b','b.warga_id','=','a.warga_id')
            ->where($this->primaryKey,$id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }


}
