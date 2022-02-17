<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Periodetagihan extends Model
{
    protected $table = 'periode_tagihan';
    protected $primaryKey = 'pt_id';
    public $timestamps = false;

    public function getItem($pt_id) {

        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.pt_id',$pt_id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
    }

    public function getList($wil_id, $sortdir = 'asc') {
		$rs = DB::select("select a.*,
        (case when a.pt_bulan = 1 then 'Januari'
              when a.pt_bulan = 2 then 'Februari'
              when a.pt_bulan = 3 then 'Maret'
              when a.pt_bulan = 4 then 'April'
              when a.pt_bulan = 5 then 'Mei'
              when a.pt_bulan = 6 then 'Juni'
              when a.pt_bulan = 7 then 'Juli'
              when a.pt_bulan = 8 then 'Agustus'
              when a.pt_bulan = 9 then 'September'
              when a.pt_bulan = 10 then 'Oktober'
              when a.pt_bulan = 11 then 'November'
              when a.pt_bulan = 12 then 'Desember'
        end) as month_name from periode_tagihan a
        where a.wil_id = ?
        order by a.pt_tahun ".$sortdir.",a.pt_bulan ".$sortdir,[$wil_id]);

        if(!empty($rs)) {
            return $rs;
        }else {
            return "";
        }
	 }

     public function getRiwayatPembayaran($wil_id, $pt_bulan, $pt_tahun, $sortdir='asc') {

        $sql = "select a.*,
        (case when a.pt_bulan = 1 then 'Januari'
              when a.pt_bulan = 2 then 'Februari'
              when a.pt_bulan = 3 then 'Maret'
              when a.pt_bulan = 4 then 'April'
              when a.pt_bulan = 5 then 'Mei'
              when a.pt_bulan = 6 then 'Juni'
              when a.pt_bulan = 7 then 'Juli'
              when a.pt_bulan = 8 then 'Agustus'
              when a.pt_bulan = 9 then 'September'
              when a.pt_bulan = 10 then 'Oktober'
              when a.pt_bulan = 11 then 'November'
              when a.pt_bulan = 12 then 'Desember'
        end) as month_name from periode_tagihan a
        where a.wil_id = ?
        and a.pt_status = 'S'";

        if($pt_bulan != '')
            $sql .= " and a.pt_bulan = ".$pt_bulan;

        if($pt_tahun != '')
            $sql .= " and a.pt_tahun = ".$pt_tahun;

        $sql .= "order by a.pt_id ".$sortdir;

		$rs = DB::select($sql,[$wil_id]);

        if(!empty($rs)) {
            return $rs;
        }else {
            return "";
        }
	 }

    public static function getMonthName($num) {
        $arrMonth = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        return $arrMonth[$num-1];
    }

}
