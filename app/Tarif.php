<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Tarif extends Model
{
    protected $table = 'tarif';
    protected $primaryKey = 'tarif_id';
    public $timestamps = false;

    public function getList($wil_id) {

        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.wil_id',$wil_id)
            ->orderBy('a.tarif_id','asc')
            ->get();

         if(count($rs) > 0) {
            return $rs;
         } else {

            DB::table('tarif')->insert([
                ['wil_id' => $wil_id, 'tarif_nama' => 'TARIF_LISTRIK_PER_KWH', 'tarif_nilai' => 0],
                ['wil_id' => $wil_id, 'tarif_nama' => 'ABODEMEN_LISTRIK', 'tarif_nilai' => 0],
                ['wil_id' => $wil_id, 'tarif_nama' => 'TARIF_AIR_PER_M3', 'tarif_nilai' => 0],
                ['wil_id' => $wil_id, 'tarif_nama' => 'ABODEMEN_AIR', 'tarif_nilai' => 0]
            ]);

            $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.wil_id',$wil_id)
            ->orderBy('a.tarif_id','asc')
            ->get();

            return $rs;
         }
    }

    public function getNilai($tarif_nama, $wil_id) {
        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.tarif_nama',$tarif_nama)
            ->where('a.wil_id',$wil_id)
            ->first();

        if(empty($rs)) {
            return 0;
        }else {
            return $rs->tarif_nilai;
        }
    }
}
