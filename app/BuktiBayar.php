<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class BuktiBayar extends Model
{
    protected $table = 'bukti_bayar';
    protected $primaryKey = 'bb_id';
    public $timestamps = false;

    protected $fillable = [
        'bb_tgl',
        'bb_bank',
        'bb_rek_no',
        'bb_rek_nama',
        'bb_cara_bayar',
        'bb_nominal',
        'bb_bukti',
        'bb_ket',
        'bb_confirm_no',
        'bb_periode',
        'warga_id',
        'bb_status_valid',
        'bb_created_at',
        'bb_validate_date'
    ];

    public function isExistData($warga_id, $txtPeriode) {
        $data = DB::select("select count(1) as is_exist from bukti_bayar
                                    where warga_id = ?
                                    and upper(bb_periode) = upper(?)
                                    and (bb_status_valid is null or bb_status_valid = 'Y')",[$warga_id, $txtPeriode]);

        if(count($data) > 0) {
            return (int)($data[0])->is_exist > 0;
        }else {
            return false;
        }
    }

    public function getDetail($bb_id) {
        $sql = "select *,
        (case
        when bb_cara_bayar = '1' then 'Bank Transfer'
        when bb_cara_bayar = '2' then 'ATM'
        when bb_cara_bayar = '3' then 'Internet Banking'
        when bb_cara_bayar = '4' then 'Mobile Banking'
        end) as cara_bayar,
        to_char(bb_tgl, 'DD-MM-YYYY') as tgl_transfer
        from bukti_bayar where bb_id = ".$bb_id;
        $data = DB::select($sql);

        if(count($data) > 0) return $data[0];
        return [];
    }

    public function getList($warga_id = '', $filter = '', $wil_id = '', $sort_dir = 'desc') {
        $sql = "select a.*,
        (case
        when a.bb_cara_bayar = '1' then 'Bank Transfer'
        when a.bb_cara_bayar = '2' then 'ATM'
        when a.bb_cara_bayar = '3' then 'Internet Banking'
        when a.bb_cara_bayar = '4' then 'Mobile Banking'
        end) as cara_bayar,
        to_char(a.bb_tgl, 'DD-MM-YYYY') as tgl_transfer
        from bukti_bayar a
        inner join warga b on a.warga_id = b.warga_id
        where true";
        if(!empty($warga_id)) {
            $sql .= " and a.warga_id = ".$warga_id;
        }

        if(!empty($wil_id)) {
            $sql .= " and b.wil_id = ".$wil_id;
        }

        if(!empty($filter)) {
            if($filter == 'not_confirmed') {
                $sql .= " and a.bb_status_valid is null";
                $sql .= " order by a.bb_id ".$sort_dir;
            }

            if($filter == 'confirmed') {
                $sql .= " and a.bb_status_valid is not null";
                $sql .= " order by a.bb_validate_date ".$sort_dir;
            }
        }

        $data = DB::select($sql);

        if(count($data) > 0) return $data;
        return [];
    }


    public function getListLimited($warga_id = '', $filter = '', $wil_id = '', $sort_dir = 'desc', $page=1, $limit=20) {

        $rs = DB::table("$this->table as a")
                ->select('a.*',
                DB::raw("(case
                when a.bb_cara_bayar = '1' then 'Bank Transfer'
                when a.bb_cara_bayar = '2' then 'ATM'
                when a.bb_cara_bayar = '3' then 'Internet Banking'
                when a.bb_cara_bayar = '4' then 'Mobile Banking'
                end) as cara_bayar"),
                 DB::raw("to_char(a.bb_tgl, 'DD-MM-YYYY') as tgl_transfer"))
                ->join('warga as b','a.warga_id','=','b.warga_id');

        if(!empty($warga_id)) {
            $rs = $rs->where('a.warga_id',$warga_id);
        }

        if(!empty($wil_id)) {
            $rs = $rs->where('b.wil_id',$wil_id);
        }

        if(!empty($filter)) {
            if($filter == 'not_confirmed') {
                $rs = $rs->whereRaw("a.bb_status_valid is null");
                $rs = $rs->orderBy('a.bb_id',$sort_dir);
            }

            if($filter == 'confirmed') {
                $rs = $rs->whereRaw("a.bb_status_valid is not null");
                $rs = $rs->orderBy('a.bb_validate_date',$sort_dir);
            }
        }

        if(!empty($page)) {
            $rs = $rs->limit($limit)->offset(($page-1)*$limit);
        }

        $rs = $rs->get();

        if(!empty($rs)) return $rs;
        return [];
    }


    public function totalKonfirmasi($warga_id = '', $filter = '', $wil_id = '') {
        $sql = "select sum(a.bb_nominal) as total_konfirmasi from bukti_bayar a
                inner join warga b on a.warga_id = b.warga_id
                where true";
        if(!empty($warga_id)) {
            $sql .= " and a.warga_id = ".$warga_id;
        }

        if(!empty($wil_id)) {
            $sql .= " and b.wil_id = ".$wil_id;
        }

        if(!empty($filter)) {
            if($filter == 'not_confirmed') {
                $sql .= " and a.bb_status_valid is null";
            }

            if($filter == 'confirmed') {
                $sql .= " and a.bb_status_valid is not null";
            }
        }

        $data = DB::select($sql);

        if(count($data) > 0) return (int)($data[0])->total_konfirmasi;
        return 0;
    }
}
