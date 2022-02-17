<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class JenisUsaha extends Model
{
    protected $table = 'jenis_usaha';
    protected $primaryKey = 'ju_id';
    public $timestamps = false;

    protected $fillable = [
        'ju_id',
        'ju_nama',
    ];
}
