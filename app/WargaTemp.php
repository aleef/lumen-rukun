<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class WargaTemp extends Model
{
    protected $table = 'warga_temp';
    protected $primaryKey = 'wt_id';
    public $timestamps = false;

}

