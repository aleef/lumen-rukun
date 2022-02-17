<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;

class Peraturan extends Model
{
    protected $table = 'peraturan';
    protected $primaryKey = 'peraturan_id';
    public $timestamps = false;

    public function getCreateDateAttribute($value) {
        return Carbon::parse($value)->isoFormat("D MMMM Y HH:mm:ss");
    }

    public function getPeraturanFotoAttribute($value) {
        return url('public/peraturan/'.$value);
    }

    public function getPeraturanFileAttribute($value) {
        if(!empty($value))
           return url('public/peraturan/'.$value);
        return '';
    }

}
