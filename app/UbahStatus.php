<?php


namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class UbahStatus extends Model
{
    
    protected $table = 'ubah_status';
    
    protected $primaryKey = 'us_id';
    
    protected $fillable = [
    	'wil_id', 'wil_nama', 'us_tgl', 'us_user_id', 'us_status_before', 'us_status_after', 'us_status_expire'
    ];
    public $timestamps = false;


}
