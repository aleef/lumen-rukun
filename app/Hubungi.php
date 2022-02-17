<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Hubungi extends Model
{
    protected $table = 'hubungi';
    protected $primaryKey = 'hub_id';
    public $timestamps = false;
}
