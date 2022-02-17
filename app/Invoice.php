<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Invoice extends Model
{
    protected $table = 'tagihan';
    protected $primaryKey = 'tag_id';
    public $timestamps = false;

    /**
     * 200001000126012021
     * 2 = Kode kita
     * 00001 = ID Wilayah
     * 0001 = ID Warga
     * 26 = Tanggal
     * 01 = Bulan
     * 2021 = Tahun
     *  */
    public static function generateInvoiceNo($wil_id, $warga_id) {
        if($wil_id == '' || $warga_id == '') return '-';
        $kodeRukun = '2';
        $idWilayah = str_pad($wil_id, 5, "0", STR_PAD_LEFT);
        $idWarga = str_pad($warga_id, 4, "0", STR_PAD_LEFT);
        $date = date('dmY');

        $tagNo = $kodeRukun.$idWilayah.$idWarga.$date;
        return $tagNo;
    }

    public function getItem($pt_id, $warga_id) {

        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.pt_id',$pt_id)
            ->where('a.warga_id',$warga_id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return "";
    }

    public function getDetail($tag_id) {
        $rs = DB::table("$this->table as a")
            ->select('a.*', DB::raw("to_char(tag_tgl_bayar, 'dd Mon YYYY HH24:MI:SS') as tgl_bayar"))
            ->where('a.tag_id',$tag_id)
            ->first();

        if(!empty($rs))
            return $rs;
        else
            return "";
    }

    public function getKategoriBangunan($kb_id) {

        $rs = DB::table("kategori_bangunan as a")
            ->select('a.*')
            ->where('a.kb_id',$kb_id)
            ->first();

         if(!empty($rs))
             return $rs;
         else
             return 0;
    }

    public function getList($pt_id) {

        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.pt_id',$pt_id)
            ->get();

         if(!empty($rs))
             return $rs;
         else
             return "";
    }

    public function getListPembayaran($pt_id, $status) {

        if(empty($status)) {
            $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.warga_nama_depan', 'b.warga_nama_belakang', 'b.warga_hp', 'b.warga_alamat', 'b.warga_no_rumah', DB::raw("(b.warga_nama_depan || ' ' || b.warga_nama_belakang ) as warga_nama"))
            ->join('warga as b','a.warga_id','=','b.warga_id')
            ->where('a.pt_id',$pt_id)
            ->get();
        }else if($status == 'belum_bayar') {
            $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.warga_nama_depan', 'b.warga_nama_belakang', 'b.warga_hp', 'b.warga_alamat', 'b.warga_no_rumah', DB::raw("(b.warga_nama_depan || ' ' || b.warga_nama_belakang ) as warga_nama"))
            ->join('warga as b','a.warga_id','=','b.warga_id')
            ->where('a.pt_id',$pt_id)
            ->where('a.tag_tgl_bayar',null)
            ->get();
        }else if($status == 'sudah_bayar') {
            $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.warga_nama_depan', 'b.warga_nama_belakang', 'b.warga_hp', 'b.warga_alamat', 'b.warga_no_rumah', DB::raw("(b.warga_nama_depan || ' ' || b.warga_nama_belakang ) as warga_nama"))
            ->join('warga as b','a.warga_id','=','b.warga_id')
            ->where('a.pt_id',$pt_id)
            ->where('a.tag_status','1')
            ->get();
        }

         if(count($rs) > 0)
             return $rs;
         else
             return [];
    }

    public function isMatch($wil_id, $pt_id) {

        $warga = DB::table('warga')
                 ->select(DB::raw('count(*) as total_warga'))
                 ->where('wil_id', $wil_id)
                 ->first();

        $tagihan = DB::table('tagihan')
                ->select(DB::raw('count(*) as total_input'))
                ->where('pt_id', $pt_id)
                ->first();


        if($warga->total_warga == $tagihan->total_input) {
            return 'Y';
        }else {
            return 'T';
        }
    }

    public function getListTagihanWithToken($pt_id) {
        $rs = DB::select("select a.tag_id, a.pt_id, a.warga_id, d.warga_nama_depan, b.fcm_token, c.pt_tahun,
        (case when c.pt_bulan = 1 then 'Januari'
        when c.pt_bulan = 2 then 'Februari'
        when c.pt_bulan = 3 then 'Maret'
        when c.pt_bulan = 4 then 'April'
        when c.pt_bulan = 5 then 'Mei'
        when c.pt_bulan = 6 then 'Juni'
        when c.pt_bulan = 7 then 'Juli'
        when c.pt_bulan = 8 then 'Agustus'
        when c.pt_bulan = 9 then 'September'
        when c.pt_bulan = 10 then 'Oktober'
        when c.pt_bulan = 11 then 'November'
        when c.pt_bulan = 12 then 'Desember'
        end) as month_name
        from tagihan a
        left join core_user b on a.warga_id = b.user_ref_id
        left join periode_tagihan c on a.pt_id = c.pt_id
        left join warga d on a.warga_id = d.warga_id
        where a.pt_id = ?",[$pt_id]);

        if(!empty($rs)) {
            return $rs;
        }else {
            return "";
        }
    }

    public function getListTunggakanWarga($wil_id) {
        $rs = DB::select("select a.warga_id, c.warga_nama_depan, c.warga_nama_belakang, c.warga_hp, c.warga_alamat, c.warga_no_rumah,
         (c.warga_nama_depan || ' ' || c.warga_nama_belakang) as warga_nama,
        sum(a.tag_total) as total_tunggakan
        from tagihan a
        inner join periode_tagihan b on a.pt_id = b.pt_id
        inner join warga c on a.warga_id = c.warga_id
        where b.pt_status = 'S'
        and c.wil_id = ?
        and a.tag_tgl_bayar is null
        and tag_status is null
        group by a.warga_id, c.warga_nama_depan, c.warga_nama_belakang, c.warga_hp, c.warga_alamat, c.warga_no_rumah",[$wil_id]);

        if(count($rs) > 0) {
            return $rs;
        }else {
            return [];
        }
    }

    public function getListPeriodeTunggakanWarga($warga_id, $includeWaitingForValidate = true) {

        if($includeWaitingForValidate) {
            $rs = DB::select("select a.tag_id, a.pt_id, c.warga_id, b.pt_bulan, b.pt_tahun,
            (case when b.pt_bulan = 1 then 'Januari'
                when b.pt_bulan = 2 then 'Februari'
                when b.pt_bulan = 3 then 'Maret'
                when b.pt_bulan = 4 then 'April'
                when b.pt_bulan = 5 then 'Mei'
                when b.pt_bulan = 6 then 'Juni'
                when b.pt_bulan = 7 then 'Juli'
                when b.pt_bulan = 8 then 'Agustus'
                when b.pt_bulan = 9 then 'September'
                when b.pt_bulan = 10 then 'Oktober'
                when b.pt_bulan = 11 then 'November'
                when b.pt_bulan = 12 then 'Desember'
            end) as month_name,
            a.tag_total,
            a.tag_status,
            a.tag_tgl_bayar,
            a.tag_due,
            (c.warga_nama_depan || ' ' || c.warga_nama_belakang) as warga_nama,
            c.warga_alamat,
            c.warga_no_rumah
            from tagihan a
            inner join periode_tagihan b on a.pt_id = b.pt_id
            inner join warga c on a.warga_id = c.warga_id
            where b.pt_status = 'S'
            and a.warga_id = ?
            and a.tag_tgl_bayar is null
            order by a.pt_id asc",[$warga_id]);
        }else {
            $rs = DB::select("select a.tag_id, a.pt_id, c.warga_id, b.pt_bulan, b.pt_tahun,
            (case when b.pt_bulan = 1 then 'Januari'
                when b.pt_bulan = 2 then 'Februari'
                when b.pt_bulan = 3 then 'Maret'
                when b.pt_bulan = 4 then 'April'
                when b.pt_bulan = 5 then 'Mei'
                when b.pt_bulan = 6 then 'Juni'
                when b.pt_bulan = 7 then 'Juli'
                when b.pt_bulan = 8 then 'Agustus'
                when b.pt_bulan = 9 then 'September'
                when b.pt_bulan = 10 then 'Oktober'
                when b.pt_bulan = 11 then 'November'
                when b.pt_bulan = 12 then 'Desember'
            end) as month_name,
            a.tag_total,
            a.tag_status,
            a.tag_tgl_bayar,
            a.tag_due,
            (c.warga_nama_depan || ' ' || c.warga_nama_belakang) as warga_nama,
            c.warga_alamat,
            c.warga_no_rumah
            from tagihan a
            inner join periode_tagihan b on a.pt_id = b.pt_id
            inner join warga c on a.warga_id = c.warga_id
            where b.pt_status = 'S'
            and a.warga_id = ?
            and a.tag_tgl_bayar is null
            and (a.tag_status is null or a.tag_status not in('2'))
            order by a.pt_id asc",[$warga_id]);
        }

        if(count($rs) > 0) {
            return $rs;
        }else {
            return [];
        }
    }

    public function getListPeriodeWaitingForConfirmationValidate($warga_id = '', $bb_id = '') {

        $sql = "select a.tag_id, a.pt_id, c.warga_id, b.pt_bulan, b.pt_tahun,
        (case when b.pt_bulan = 1 then 'Januari'
            when b.pt_bulan = 2 then 'Februari'
            when b.pt_bulan = 3 then 'Maret'
            when b.pt_bulan = 4 then 'April'
            when b.pt_bulan = 5 then 'Mei'
            when b.pt_bulan = 6 then 'Juni'
            when b.pt_bulan = 7 then 'Juli'
            when b.pt_bulan = 8 then 'Agustus'
            when b.pt_bulan = 9 then 'September'
            when b.pt_bulan = 10 then 'Oktober'
            when b.pt_bulan = 11 then 'November'
            when b.pt_bulan = 12 then 'Desember'
        end) as month_name,
        a.tag_total,
        a.tag_status,
        a.tag_tgl_bayar,
        (c.warga_nama_depan || ' ' || c.warga_nama_belakang) as warga_nama,
        c.warga_alamat,
        c.warga_no_rumah
        from bb_detil d
        inner join tagihan a on d.tag_id = a.tag_id
        inner join periode_tagihan b on a.pt_id = b.pt_id
        inner join warga c on a.warga_id = c.warga_id
        where true
        ";

        if(!empty($bb_id)) {
            $sql .= " and d.bb_id = '".$bb_id."'";
        }

        if(!empty($warga_id)) {
            $sql .= " and a.warga_id = ".$warga_id;
        }

        $sql .= " order by a.pt_id asc";
        $rs = DB::select($sql);

        if(count($rs) > 0) {
            return $rs;
        }else {
            return [];
        }
    }

    public function getItemTunggakanWargaPerPeriode($warga_id, $pt_bulan, $pt_tahun) {
        $rs = DB::select("select a.tag_id, a.pt_id, b.pt_bulan, b.pt_tahun,
        (case when b.pt_bulan = 1 then 'Januari'
            when b.pt_bulan = 2 then 'Februari'
            when b.pt_bulan = 3 then 'Maret'
            when b.pt_bulan = 4 then 'April'
            when b.pt_bulan = 5 then 'Mei'
            when b.pt_bulan = 6 then 'Juni'
            when b.pt_bulan = 7 then 'Juli'
            when b.pt_bulan = 8 then 'Agustus'
            when b.pt_bulan = 9 then 'September'
            when b.pt_bulan = 10 then 'Oktober'
            when b.pt_bulan = 11 then 'November'
            when b.pt_bulan = 12 then 'Desember'
        end) as month_name,
        a.tag_total,
        a.tag_status,
        to_char(a.tag_tgl_bayar, 'dd-Mon-yyyy HH24:MI:SS') as tag_tgl_bayar
        from tagihan a
        inner join periode_tagihan b on a.pt_id = b.pt_id
        where b.pt_status = 'S'
        and a.warga_id = ?
        ",[$warga_id]);

        if(count($rs) > 0) {
            return $rs;
        }else {
            return null;
        }
    }

    public function getAmountTotalTunggakanWarga($warga_id) {
        $tunggakan = DB::select("select sum(a.tag_total) total_tunggakan
                        from tagihan a
                        inner join periode_tagihan b on a.pt_id = b.pt_id
                        where b.pt_status = 'S'
                        and a.warga_id = ?
                        and (a.tag_status is null or a.tag_status not in('2'))
                        and a.tag_tgl_bayar is null",[$warga_id]);

        if(count($tunggakan) > 0) {
            return ($tunggakan[0])->total_tunggakan;
        }else {
            return 0;
        }
    }

    public function getAmountTotalWaitingForConfirmationValidate($warga_id = '', $bb_id = '') {

        $sql = "select sum(bb_nominal) as total_konfirmasi from bukti_bayar where true";
        if(!empty($warga_id)) {
            $sql .= " and warga_id = ".$warga_id;
        }
        if(!empty($bb_id)) {
            $sql .= " and bb_id = ".$bb_id;
        }

        $tunggakan = DB::select($sql);

        if(count($tunggakan) > 0) {
            return ($tunggakan[0])->total_konfirmasi;
        }else {
            return 0;
        }

        // $tunggakan = DB::select("select sum(a.tag_total) total_tunggakan
        //                 from tagihan a
        //                 inner join periode_tagihan b on a.pt_id = b.pt_id
        //                 where b.pt_status = 'S'
        //                 and a.warga_id = ?
        //                 and a.tag_status = '2'
        //                 and a.tag_tgl_bayar is null",[$warga_id]);

    }

    public function getAmountTotalTagihanPerBuktiBayar($bb_id = '') {

       $sql = "select sum(b.tag_total) as total_tagihan
                from bb_detil a
                inner join tagihan b on a.tag_id = b.tag_id
                where a.bb_id = ".$bb_id;

        $tunggakan = DB::select($sql);

        if(count($tunggakan) > 0) {
            return ($tunggakan[0])->total_tagihan;
        }else {
            return 0;
        }
    }

    public function getListTunggakanWargaPerPeriode($pt_id) {
        $rs = DB::select("select a.pt_id, a.tag_id, a.warga_id, (c.warga_nama_depan || ' ' || c.warga_nama_belakang) as warga_nama, c.warga_alamat, c.warga_no_rumah, c.warga_hp,
        (case when b.pt_bulan = 1 then 'Januari'
                when b.pt_bulan = 2 then 'Februari'
                when b.pt_bulan = 3 then 'Maret'
                when b.pt_bulan = 4 then 'April'
                when b.pt_bulan = 5 then 'Mei'
                when b.pt_bulan = 6 then 'Juni'
                when b.pt_bulan = 7 then 'Juli'
                when b.pt_bulan = 8 then 'Agustus'
                when b.pt_bulan = 9 then 'September'
                when b.pt_bulan = 10 then 'Oktober'
                when b.pt_bulan = 11 then 'November'
                when b.pt_bulan = 12 then 'Desember'
                end) as month_name,
                b.pt_tahun,
                a.tag_total,
                a.tag_status,
                a.tag_tgl_bayar
        from tagihan a
        inner join periode_tagihan b on a.pt_id = b.pt_id
        inner join warga c on a.warga_id = c.warga_id
        where b.pt_status = 'S'
        and b.pt_id = ?
        and a.tag_tgl_bayar is null",[$pt_id]);

        if(count($rs) > 0) {
            return $rs;
        }else {
            return [];
        }
    }

    public function totalAll($pt_id) {

        $totalAll = DB::table('tagihan')
        ->select(DB::raw('count(*) as total_input'))
        ->where('pt_id', $pt_id)
        ->first();

        $totalBelumBayar = DB::table('tagihan')
        ->select(DB::raw('count(*) as total_input'))
        ->where('pt_id', $pt_id)
        ->where('tag_tgl_bayar','=',null)
        ->first();

        $totalSudahBayar = DB::table('tagihan')
        ->select(DB::raw('count(*) as total_input'))
        ->where('pt_id', $pt_id)
        ->where('tag_status','=','1')
        ->first();

        return array('all' => $totalAll->total_input, 'belum_bayar' => $totalBelumBayar->total_input, 'sudah_bayar' => $totalSudahBayar->total_input);
    }


    //METHOD WARGA
    public function getListPeriodeTagihanWarga($warga_id) {
        $rs = DB::select("select a.tag_id, a.pt_id, c.warga_id, b.pt_bulan, b.pt_tahun, a.tag_tgl_bayar, a.tag_bukti_bayar, a.tag_cara_bayar,
        (case when b.pt_bulan = 1 then 'Januari'
            when b.pt_bulan = 2 then 'Februari'
            when b.pt_bulan = 3 then 'Maret'
            when b.pt_bulan = 4 then 'April'
            when b.pt_bulan = 5 then 'Mei'
            when b.pt_bulan = 6 then 'Juni'
            when b.pt_bulan = 7 then 'Juli'
            when b.pt_bulan = 8 then 'Agustus'
            when b.pt_bulan = 9 then 'September'
            when b.pt_bulan = 10 then 'Oktober'
            when b.pt_bulan = 11 then 'November'
            when b.pt_bulan = 12 then 'Desember'
        end) as month_name,
        a.tag_total,
        a.tag_status,
        (c.warga_nama_depan || ' ' || c.warga_nama_belakang) as warga_nama,
        c.warga_alamat,
        c.warga_no_rumah
        from tagihan a
        inner join periode_tagihan b on a.pt_id = b.pt_id
        inner join warga c on a.warga_id = c.warga_id
        where b.pt_status = 'S'
        and a.warga_id = ?
        order by a.tag_id desc",[$warga_id]);

        if(count($rs) > 0) {
            return $rs;
        }else {
            return [];
        }
    }

    public function getInfoPeriodeTunggakan($warga_id) {
        $rs = DB::select("select a.tag_id, a.pt_id, b.pt_bulan, b.pt_tahun,
        (case when b.pt_bulan = 1 then 'Januari'
            when b.pt_bulan = 2 then 'Februari'
            when b.pt_bulan = 3 then 'Maret'
            when b.pt_bulan = 4 then 'April'
            when b.pt_bulan = 5 then 'Mei'
            when b.pt_bulan = 6 then 'Juni'
            when b.pt_bulan = 7 then 'Juli'
            when b.pt_bulan = 8 then 'Agustus'
            when b.pt_bulan = 9 then 'September'
            when b.pt_bulan = 10 then 'Oktober'
            when b.pt_bulan = 11 then 'November'
            when b.pt_bulan = 12 then 'Desember'
        end) as month_name,
        a.tag_total
        from tagihan a
        inner join periode_tagihan b on a.pt_id = b.pt_id
        where b.pt_status = 'S'
        and a.warga_id = ?
        and a.tag_tgl_bayar is null
        and (a.tag_status is null or a.tag_status not in('2'))
        order by a.pt_id asc",[$warga_id]);

        if(count($rs) > 0) {
            $totalRecord = count($rs);

            $startTahun = ($rs[0])->pt_tahun;
            $endTahun = ($rs[$totalRecord-1])->pt_tahun;

            $startMonth = ($rs[0])->month_name;
            $endMonth = ($rs[$totalRecord-1])->month_name;

            $startPeriode = $startMonth.' '.$startTahun;
            $endPeriode = $endMonth.' '.$endTahun;

            if($startPeriode == $endPeriode) {
                return $startPeriode;
            }

            if($startTahun == $endTahun) {
                return $startMonth.' - '.$endMonth.' '.$endTahun;
            }

            return $startPeriode.' - '.$endPeriode;
        }else {
            return '';
        }
    }

}
