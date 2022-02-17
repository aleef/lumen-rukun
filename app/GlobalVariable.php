<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class GlobalVariable extends Model
{
	protected $table = 'global_variable';
    protected $primaryKey = 'global_id';
    public $timestamps = true;
}