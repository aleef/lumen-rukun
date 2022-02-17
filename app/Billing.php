<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;


class Billing extends Model
{
    protected $table = 'billing';
    protected $primaryKey = 'bil_id';
    public $timestamps = false;

    protected $fillable = [
        'bil_id',
        'pl_id',
        'wil_id',
        'wil_nama',
        'bil_date',
        'bil_no',
        'bil_jumlah',
        'bil_mulai',
        'bil_akhir',
        'bil_due',
        'bil_status',
        'bil_tgl_bayar',
        'bil_cara_bayar',
        'bil_jml_bayar',
        'bil_bukti',
        'order_no',
        'bil_catatan',
    ];

    /**
     * 20000126012021
     * 2 = Kode kita
     * 00001 = ID Wilayah
     * 26 = Tanggal
     * 01 = Bulan
     * 2021 = Tahun
     *  */
    public static function generateBillNo($wil_id) {
        if($wil_id == '') return '-';
        $kodeRukun = '2';
        $idWilayah = str_pad($wil_id, 5, "0", STR_PAD_LEFT);
        $date = date('dmY');

        $billNo = $kodeRukun.$idWilayah.$date;
        return $billNo;
    }

    public function getList($wil_id) {
        $rs = DB::table("billing as a")
                ->select('a.*', 'b.*')
                ->join('paket_langganan as b','a.pl_id','=','b.pl_id')
                ->where('wil_id',$wil_id)
                ->whereNull('bil_tgl_bayar')
                ->whereNull('bil_status')
                ->orderBy('bil_id', 'desc')
                ->limit(1)
                ->get();

        if(!empty($rs)) return $rs;
        return [];
    }

    public function getListPembayaran($wil_id, $page=1, $limit = 20) {
        $rs = DB::table("billing as a")
                ->select('a.*', 'b.*')
                ->join('paket_langganan as b','a.pl_id','=','b.pl_id')
                ->where('wil_id',$wil_id)
                ->where('bil_status','1')
                ->whereNotNull('bil_tgl_bayar')
                ->orderBy('bil_id', 'desc');

        if(!empty($page)) {
            $rs = $rs->limit($limit)->offset(($page-1)*$limit);
        }

        $rs = $rs->get();

        if(!empty($rs)) return $rs;
        return [];
    }

    public function getRecentBilling($wil_id) {
        $rs = DB::table("billing as a")
                ->select('a.*', 'b.*')
                ->join('paket_langganan as b','a.pl_id','=','b.pl_id')
                ->where('wil_id',$wil_id)
                ->whereNull('bil_tgl_bayar')
                ->whereNull('bil_status')
                ->orderBy('bil_id', 'desc')
                ->first();

        return $rs;
    }

    public function getTotalTagihan($wil_id) {
        $rs = DB::table("billing")
            ->select('*')
            ->where('wil_id',$wil_id)
            ->whereNull('bil_tgl_bayar')
            ->whereNull('bil_status')
            ->first();

        if(!empty($rs)) return $rs->bil_jumlah;

        return 0;
    }

    public function getBilling($wil_id) {
        $rs = DB::table("billing as a")
                ->select('a.*', 'b.*', 'c.wil_nama')
                ->join('paket_langganan as b','a.pl_id','=','b.pl_id')
                ->join('wilayah as c','c.wil_id','=','a.wil_id')
                ->where([['a.wil_id',"=",$wil_id],['a.bil_status',"=", "1"]])
                ->orderBy('bil_id','desc')
                ->limit(1)
                ->get();

        if(!empty($rs)) return $rs;
    }
}
