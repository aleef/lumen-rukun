<?php


namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Sales extends Model
{
    
    protected $table = 'sales';
    
    protected $fillable = [
    	'sales_nama', 'sales_hp', 'sales_email', 'sales_head', 'sales_parent_id', 'sales_kode'
    ];
    protected $primaryKey = 'sales_id';
    public $timestamps = false;


}
