<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;

class PaketLangganan extends Model
{
    protected $table = 'paket_langganan';
    protected $primaryKey = 'pl_id';
    public $timestamps = false;

    protected $fillable = [
        'pl_id',
        'pl_nama',
        'pl_bulan',
        'pl_harga',
        'pl_mulai_berlaku',
        'pl_status',
        'pl_ket_harga',
        'pl_ket_free',
        'pl_description'
    ];

    public function getUpgradeList($wil_id, $jml_warga=0) {
        // $sql = "select * from paket_langganan
        // where pl_id not in (
        //   select pl_id from billing
        //   where wil_id = ? and bil_tgl_bayar is not null
        //   ORDER BY bil_id DESC
        //   LIMIT 1
        // ) and pl_status = '1'";

         $sql = "select * from paket_langganan
        where pl_id not in (
          select pl_id from wilayah
          where wil_id = ?
          LIMIT 1
        ) and pl_status = '1'
        and pl_manual = 'T'";

        if(!empty($jml_warga)) {
            $sql .= " and pl_maks_warga > ".$jml_warga;
        }

        $rs = DB::select($sql,[$wil_id]);

        if(count($rs) > 0) {
            return $rs;
        }else {
            return [];
        }
    }

    public function getExpireTrialDate($wil_id) {
        $globalVar = GlobalVariable::where('global_name','trial')->first();
        $dayTrial = $globalVar->global_value;

        $wilayah = Wilayah::find($wil_id);
        return Carbon::parse($wilayah->wil_mulai_trial)->addDays((int)$dayTrial)->isoFormat('D MMMM Y');
    }

    public function getExpireTrialRemainDays($wil_id) {
        $globalVar = GlobalVariable::where('global_name','trial')->first();
        $dayTrial = $globalVar->global_value;

        $wilayah = Wilayah::find($wil_id);
        return Carbon::now()->diffInDays(Carbon::parse($wilayah->wil_mulai_trial)->addDays((int)$dayTrial), false);
    }

    public function getDetail($pl_id) {
        $rs = DB::table("paket_langganan as a")
                ->select('a.*')
                ->where('pl_id',$pl_id)
                ->first();

        if(!empty($rs)) return $rs;
    }
    public function get_list($keyword='')
    {
       $rs = DB::table("paket_langganan as a")
           ->select('a.*');

       if($keyword!=''){
           $rs = $rs->where(function($q) use ($keyword) {
                   $q->where('a.pl_nama','ilike',"%$keyword%");
           });
       }

       $rs = $rs->get();

       if(!empty($rs))
            return $rs;
       else
            return "";
    }

}
