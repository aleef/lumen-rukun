<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class AnggotaKeluarga extends Model
{
    protected $table = 'anggota_keluarga';
    protected $primaryKey = 'ak_id';
    public $timestamps = false;

    protected $fillable = [
        'ak_id',
        'warga_id',
        'ak_nama',
        'ak_jk',
        'ak_hubungan',
    ];
}
