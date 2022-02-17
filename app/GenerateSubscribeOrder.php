<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class GenerateSubscribeOrder extends Model
{
    protected $table = 'generate_subscribe_order';
    protected $primaryKey = 'order_no';
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_no',
        'pl_id',
        'bil_id',
        'wil_id',
        'warga_id',
        'created_date',
        'vw_id',
        'nominal_discount'
    ];
}
