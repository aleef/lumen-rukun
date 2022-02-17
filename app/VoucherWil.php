<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class VoucherWil extends Model
{
    protected $table = 'voucher_wil';
    protected $primaryKey = 'vw_id';
    public $timestamps = false;
}
