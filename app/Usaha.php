<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;

class Usaha extends Model
{
    protected $table = 'usaha';
    protected $primaryKey = 'usaha_id';
    public $timestamps = false;

    protected $fillable = [
        'usaha_id',
        'ju_id',
        'warga_id',
        'usaha_nama',
        'usaha_wa',
        'usaha_foto',
        'usaha_sts',
        'usaha_lokasi',
        'usaha_geo',
        'wil_id',
    ];

    public function getList($wil_id, $keyword) {
        $rs = DB::table("$this->table as a")
             ->select('a.*', 'b.ju_nama')
             ->join('jenis_usaha as b','a.ju_id','=','b.ju_id');

        if(!empty($wil_id))
            $rs = $rs->where('a.wil_id', $wil_id);

        if(!empty($keyword)) {
            $rs = $rs->where(function($q) use ($keyword) {
                $q->where('a.usaha_nama','ilike',"%$keyword%")
                ->orWhere('b.ju_nama','ilike',"%$keyword%");
            });
        }

        $rs = $rs->orderBy('a.usaha_nama','asc');
        $rs = $rs->get();

        return $rs;
    }

    public function getListLimited($wil_id, $keyword, $page=1, $limit=20) {
        $rs = DB::table("$this->table as a")
             ->select('a.*', 'b.ju_nama')
             ->join('jenis_usaha as b','a.ju_id','=','b.ju_id');

        if(!empty($wil_id))
            $rs = $rs->where('a.wil_id', $wil_id);

        if(!empty($keyword)) {
            $rs = $rs->where(function($q) use ($keyword) {
                $q->where('a.usaha_nama','ilike',"%$keyword%")
                ->orWhere('b.ju_nama','ilike',"%$keyword%");
            });
        }

        $rs = $rs->orderBy('a.usaha_nama','asc');
        if(!empty($page)) {
            $rs = $rs->limit($limit)->offset(($page-1)*$limit);
        }

        $rs = $rs->get();

        return $rs;
    }

    public function statusHariIni($usaha_id) {
        $jadwalBuka = JadwalBuka::where('usaha_id',$usaha_id)->first();
        $hari = Carbon::now()->dayOfWeek;

        $mapJadwalLibur = [
            [$jadwalBuka->jb_ming_libur, $jadwalBuka->jb_ming_buka." - ".$jadwalBuka->jb_ming_tutup, $jadwalBuka->jb_ming_buka, $jadwalBuka->jb_ming_tutup],
            [$jadwalBuka->jb_sen_libur, $jadwalBuka->jb_sen_buka." - ".$jadwalBuka->jb_sen_tutup, $jadwalBuka->jb_sen_buka, $jadwalBuka->jb_sen_tutup],
            [$jadwalBuka->jb_sel_libur, $jadwalBuka->jb_sel_buka." - ".$jadwalBuka->jb_sel_tutup, $jadwalBuka->jb_sel_buka, $jadwalBuka->jb_sel_tutup],
            [$jadwalBuka->jb_rab_libur, $jadwalBuka->jb_rab_buka." - ".$jadwalBuka->jb_rab_tutup, $jadwalBuka->jb_rab_buka, $jadwalBuka->jb_rab_tutup],
            [$jadwalBuka->jb_kam_libur, $jadwalBuka->jb_kam_buka." - ".$jadwalBuka->jb_kam_tutup, $jadwalBuka->jb_kam_buka, $jadwalBuka->jb_kam_tutup],
            [$jadwalBuka->jb_jum_libur, $jadwalBuka->jb_jum_buka." - ".$jadwalBuka->jb_jum_tutup, $jadwalBuka->jb_jum_buka, $jadwalBuka->jb_jum_tutup],
            [$jadwalBuka->jb_sab_libur, $jadwalBuka->jb_sab_buka." - ".$jadwalBuka->jb_sab_tutup, $jadwalBuka->jb_sab_buka, $jadwalBuka->jb_sab_tutup],
        ];

        $statusLibur = $mapJadwalLibur[$hari][0];
        $jamBukaHariIni = $mapJadwalLibur[$hari][1];

        $jamBuka = (int) str_replace(":","",$mapJadwalLibur[$hari][2]);
        $jamTutup = (int) str_replace(":","",$mapJadwalLibur[$hari][3]);

        $nowHourMinutes = (int) str_replace(":","",Carbon::now()->format('H:i'));


        $openClose = '';
        if($statusLibur == '1') {
            $openClose = 'Tutup';
        } else {
            $openClose = 'Buka';

            if($jamBuka == 0 && $jamTutup == 0) {
                $openClose = 'Buka';
            }else {
                if($jamTutup != 0) {
                    $openClose = ($nowHourMinutes >= $jamBuka && $nowHourMinutes <=$jamTutup) ? "Buka" : "Tutup";
                }else {
                    $openClose = ($nowHourMinutes >= $jamBuka) ? "Buka" : "Tutup";
                }
            }
        }

        return array(
            'status' => $openClose,
            'jam_buka' => $jamBukaHariIni,
            'status_libur' => $statusLibur,
        );
    }

}
