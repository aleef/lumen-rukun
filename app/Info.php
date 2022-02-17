<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;

class Info extends Model
{
    protected $table = 'informasi';
    protected $primaryKey = 'info_id';
    public $timestamps = false;

    /* Get List */
     public function get_list($keyword='', $wil_id='', $info_kat='', $limit=0)
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id');

        if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

        if($info_kat!='')
            $rs = $rs->where('a.info_kat',$info_kat);


        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.info_judul','ilike',"%$keyword%")
                    ->orWhere('a.info_isi','ilike',"%$keyword%");
            });
        }

		$rs = $rs->orderBy('a.info_id', 'desc');

		if($limit != 0) {
			$rs = $rs->limit($limit);
		}

        $rs = $rs->get();

         if(!empty($rs)){

            $result = array();
            $i=0;
            foreach($rs as $row){

                if($row->info_img == ''){
                    $img = 'default.jpg';
                }else{
                    $img = URL('public/img/info/'.$row->info_img);
                }

                $result[$i]['info_id'] = $row->info_id;
                $result[$i]['info_judul'] = $row->info_judul;
                $result[$i]['info_date'] = Carbon::parse($row->info_date)->diffForHumans();
                $result[$i]['info_img'] = $img;
                $i++;
            }

            return $result;
         }
         else
             return [];
     }

     public function get_list_limited($keyword='', $wil_id='', $info_kat='', $page=1, $limit=20)
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id');

        if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

        if($info_kat!='')
            $rs = $rs->where('a.info_kat',$info_kat);


        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.info_judul','ilike',"%$keyword%")
                    ->orWhere('a.info_isi','ilike',"%$keyword%");
            });
        }

		$rs = $rs->orderBy('a.info_id', 'desc');

		if(!empty($page)) {
            $rs = $rs->limit($limit)->offset(($page-1)*$limit);
        }

        $rs = $rs->get();

         if(!empty($rs)){

            $result = array();
            $i=0;
            foreach($rs as $row){

                if($row->info_img == ''){
                    $img = 'default.jpg';
                }else{
                    $img = URL('public/img/info/'.$row->info_img);
                }

                $result[$i]['info_id'] = $row->info_id;
                $result[$i]['info_judul'] = $row->info_judul;
                $result[$i]['info_date'] = Carbon::parse($row->info_date)->diffForHumans();
                $result[$i]['info_img'] = $img;
                $i++;
            }

            return $result;
         }
         else
             return [];
     }


     public function get_list_undangan($keyword='', $wil_id='', $type='coming',$page=1, $limit=20) {
        $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id');

        if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

        $rs = $rs->where('a.info_kat','2');

        if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.info_judul','ilike',"%$keyword%")
                    ->orWhere('a.info_isi','ilike',"%$keyword%");
            });
        }


        if($type == 'coming') {
            $rs = $rs->where(DB::raw('info_date::date'), '>=', date('Y-m-d'));
        }else {
            $rs = $rs->where(DB::raw('info_date::date'), '<', date('Y-m-d'));
        }

        $rs = $rs->orderBy('a.info_id', 'desc');
        if(!empty($page)) {
            $rs = $rs->limit($limit)->offset(($page-1)*$limit);
        }

        $rs = $rs->get();

        if(!empty($rs)){

            $result = array();
            $i=0;
            foreach($rs as $row){

                if($row->info_img == ''){
                    $img = 'default.jpg';
                }else{
                    $img = URL('public/img/info/'.$row->info_img);
                }

                $result[$i]['info_id'] = $row->info_id;
                $result[$i]['info_judul'] = $row->info_judul;
                $result[$i]['info_date_formatted'] = Carbon::parse($row->info_date)->isoFormat('D MMMM Y');
                $result[$i]['info_date'] = Carbon::parse($row->info_date)->format('Y-m-d');
                $result[$i]['info_img'] = $img;
                $i++;
            }

            return $result;
         }
         else
             return [];

     }

     /* Get Detail */
     public function get_detail($id)
     {
         $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*', 'c.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id')
            ->where($this->primaryKey,$id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }

     /* Get Detail */
     public function get_notif_undangan()
     {
        $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id')
            ->where('a.info_kat', '2')
            ->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }


}
