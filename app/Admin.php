<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Admin extends Model
{
    protected $table = 'admin';
    protected $primaryKey = 'admin_id';
    public $timestamps = true;

    
     /* Get List */
     public function get_list()
     {
         $rs = DB::table("$this->table as a")
             ->select('a.admin_id','a.nama as name', 'b.user_email', 'b.user_type', 'a.created_at')
             ->join('core_user as b','a.admin_id','=','b.user_ref_id')
             ->where('b.user_type',1)
             ->get();
             
         if(count($rs)!=NULL)
             return $rs;
         else
             return "";
     }
     
     /* Get Detail */
     public function get_detail($id)
     {
         $rs = DB::table($this->table)
            ->select('a.admin_id','a.nama as name', 'b.user_email', 'b.user_type', 'a.created_at')
            ->join('core_user as b','a.admin_id','=','b.user_ref_id')
            ->where($this->primaryKey,$id)
            ->first();
 
         if(count($rs)!=NULL)
             return $rs;
         else
             return "";
     }

     

}