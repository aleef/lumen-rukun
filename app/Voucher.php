<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Voucher extends Model
{
    protected $table = 'voucher';
    protected $primaryKey = 'v_id';
    public $timestamps = false;
}
