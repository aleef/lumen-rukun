<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class WargaUndang extends Model
{
    protected $table = 'warga_undang';
    protected $primaryKey = 'undang_id';
    public $timestamps = false;
}
