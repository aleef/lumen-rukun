<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class JadwalBuka extends Model
{
    protected $table = 'jadwal_buka';
    protected $primaryKey = 'jb_id';
    public $timestamps = false;

    protected $fillable = [
        'jb_id',
        'usaha_id',
        'jb_ming_libur',
        'jb_ming_buka',
        'jb_ming_tutup',
        'jb_sen_libur',
        'jb_sen_buka',
        'jb_sen_tutup',
        'jb_sel_libur',
        'jb_sel_buka',
        'jb_sel_tutup',
        'jb_rab_libur',
        'jb_rab_buka',
        'jb_rab_tutup',
        'jb_kam_libur',
        'jb_kam_buka',
        'jb_kam_tutup',
        'jb_jum_libur',
        'jb_jum_buka',
        'jb_jum_tutup',
        'jb_sab_libur',
        'jb_sab_buka',
        'jb_sab_tutup',
    ];


}
