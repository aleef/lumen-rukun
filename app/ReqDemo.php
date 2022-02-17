<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class ReqDemo extends Model
{
    protected $table = 'request_demo';
    protected $fillable = [
    	'rd_nama', 'rd_hp', 'rd_email', 'kabkota_id', 'rd_jenis_wilayah', 'rd_jml_warga', 'rd_status_fu', 'rd_tgl_fu', 'sales_id'
    ];
    protected $primaryKey = 'rd_id';
    public $timestamps = false;   


}
