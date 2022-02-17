<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Wilayah extends Model
{
    protected $table = 'wilayah';
    protected $primaryKey = 'wil_id';
    public $timestamps = false;


    public static function validateSubscription($wil_id='') {
        if(empty($wil_id)) return '';

        $wilayah = Wilayah::find($wil_id);
        if($wilayah->wil_status == '2' || $wilayah->wil_status == '3') //berhenti trial
            return 'Maaf, masa trial wilayah Anda sudah habis. Silahkan berlangganan. Terima kasih.';
        if($wilayah->wil_status == '5' || $wilayah->wil_status == '6') //berhenti berlangganan
            return 'Maaf, masa berlangganan wilayah Anda sudah habis. Silahkan perpanjang langganan. Terima kasih.';

        return '';
    }

    /*=== Get List All ===*/
    public function get_list_all()
    {
        $rs = DB::table("$this->table as a")
            ->select('a.wil_id')
            ->get();

        if(count($rs)!=NULL)
            return $rs;
        else
            return "";
    }

    /*=== Get List All ===*/
    public function get_list_wilayah()
    {
        $rs = DB::table("$this->table as a")
            ->get();

        if(count($rs)!=NULL)
            return $rs;
        else
            return "";
    }

    /*=== Get List ===*/
    public function get_list()
    {
        $rs = DB::table("$this->table as a")
            ->select('a.wil_id','a.wilayah_nama as name', 'b.user_email', 'b.user_type', 'a.created_at')
            ->join('core_user as b','a.wil_id','=','b.user_ref_id')
            ->where('b.user_type',2)
            ->get();

        if(count($rs)!=NULL)
            return $rs;
        else
            return "";
    }

    /*=== Get Wilayah ===*/
     public function get_wilayah($wil_kode='')
     {

         $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*','d.*','e.*')
            ->join('wilayah as b','b.wil_id','=','a.wil_id')
            ->join('kelurahan as c','c.kel_id','=','b.kel_id')
            ->join('kecamatan as d','d.kec_id','=','c.kec_id')
            ->join('kabkota as e','e.kabkota_id','=','d.kabkota_id')
            ->where('b.wil_kode',$wil_kode)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
     }



    /*=== Get Detail ===*/
    public function get_detail($id)
    {
        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where($this->primaryKey,$id)
            ->first();

        if(!empty($rs))
            return $rs;
        else
            return "";
    }


    /*=== Get Detail ===*/
    public function get_detail_by_wil($id='', $this_month='', $last_month='')
    {

        $rs = DB::table("$this->table as a")
            ->select('a.*','b.*','c.*','d.*'
                // DB::raw('(select count(undang_id) from warga_undang where wil_id = a.wil_id) as total_undang_warga'),
                // DB::raw('(select count(warga_id) from warga where wil_id = a.wil_id) as total_warga'),
                // DB::raw('(select count(komp_id) from komplain where wil_id = a.wil_id) as total_komplain'),
                // DB::raw('(select sum(a.tag_total) from tagihan as a
                //     inner join warga as b on b.warga_id = a.warga_id
                //     where b.wil_id = '.$id.') as total_tagihan'),
                // DB::raw("(select count(a.warga_id) from tagihan as a
                //     inner join warga as b on b.warga_id = a.warga_id
                //     where b.wil_id = ".$id." AND a.tag_status = '') as total_warga_belum_bayar"),
                // DB::raw('(select sum(keu_nominal) from keuangan where wil_id = a.wil_id) as total_saldo'),
                // DB::raw("(select sum(keu_nominal) from keuangan where wil_id = a.wil_id AND keu_status = '1') as total_masuk"),
                // DB::raw("(select sum(keu_nominal) from keuangan where wil_id = a.wil_id AND keu_status = '0') as total_keluar")
                )
            ->join('kelurahan as b','b.kel_id','=','a.kel_id')
            ->join('kecamatan as c','c.kec_id','=','b.kec_id')
            ->join('kabkota as d','d.kabkota_id','=','c.kabkota_id')
            ->where($this->primaryKey,$id)
            ->first();

        if(!empty($rs))
            return $rs;
        else
            return "";
    }

    /*=== Get Trial ===*/
    public function get_notif_trial_h5()
    {
        $rs = DB::table("$this->table as a")
            ->select('a.wil_id','a.wil_mulai_trial', 'a.wil_retensi_trial')
            ->where('a.wil_status', 1)
            ->get();

        if(count($rs)!=NULL)
            return $rs;
        else
            return "";
    }


    public function get_notif_trial_expired()
    {
        $rs = DB::table("$this->table as a")
            ->select('a.wil_id','a.wil_mulai_trial', 'a.wil_retensi_trial')
            ->where('a.wil_status', 1)
            ->get();

        if(count($rs)!=NULL)
            return $rs;
        else
            return "";
    }


    public function get_notif_trial_retensi()
    {
        $rs = DB::table("$this->table as a")
            ->select('a.wil_id','a.wil_mulai_trial', 'a.wil_retensi_trial')
            ->where('a.wil_status', 2)
            ->get();

        if(count($rs)!=NULL)
            return $rs;
        else
            return "";
    }

    public function get_notif_billing()
    {
        $rs = DB::table("$this->table as a")
            ->select('a.wil_id','a.wil_expire')
            ->where('a.wil_status', 4)
            ->get();

        if(count($rs)!=NULL)
            return $rs;
        else
            return "";
    }


    public function get_notif_billing_retensi()
    {
        $rs = DB::table("$this->table as a")
            ->select('a.wil_id','a.wil_expire')
            ->where('a.wil_status', 4)
            ->get();

        if(count($rs)!=NULL)
            return $rs;
        else
            return "";
    }

    public function get_notif_billing_retensi_expired()
    {
        $rs = DB::table("$this->table as a")
            ->select('a.wil_id','a.wil_expire')
            ->where('a.wil_status', 5)
            ->get();

        if(count($rs)!=NULL)
            return $rs;
        else
            return "";
    }

    /*=== Get Wilayah by Kode ===*/
    public function get_wilayah_by_kode($id)
    {
        $rs = DB::table("$this->table as a")
            ->select('a.wil_id','a.wil_nama')
            ->where('wil_kode',$id)
            ->first();

        if(!empty($rs))
            return $rs;
        else
            return "";
    }
}
