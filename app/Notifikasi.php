<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'notif_id';
    public $timestamps = false;

    protected $fillable = [
        'notif_id',
        'warga_id',
        'notif_title',
        'notif_body',
        'notif_page',
        'page_id',
        'page_sts',
        'is_read',
        'notif_date'
    ];

    public function getNotifDateAttribute($value) {
        return Carbon::parse($value)->diffForHumans();
    }
}
