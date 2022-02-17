<?php


namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class RetensiTrial extends Model
{
    
    protected $table = 'global_variable';
    
    protected $primaryKey = 'global_id';
    
    protected $fillable = [
    	'global_name', 'global_value'
    ];
    public $timestamps = false;


}
