<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contoh extends Model

{

    protected $table = 'jenis_usaha';
     protected $fillable = [
        'ju_nama'
    ];

    protected $primaryKey = 'ju_id';
    public $timestamps = false;
}