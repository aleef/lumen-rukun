<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class RequestPaket extends Model
{
    protected $table = 'request_paket';
    protected $primaryKey = 'rp_id';
    public $timestamps = false;


}

