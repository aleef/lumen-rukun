<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class BBDetil extends Model
{
    protected $table = 'bb_detil';
    protected $primaryKey = 'bb_detil_id';
    public $timestamps = false;

    protected $fillable = [
        'bb_id',
        'tag_id'
    ];
}
