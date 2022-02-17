<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class GenerateOrder extends Model
{
    protected $table = 'generate_order';
    protected $primaryKey = 'order_no';
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_no',
        'tag_ids',
    ];
}
