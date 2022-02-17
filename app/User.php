<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class User extends Model
{
    protected $table = 'core_user';
    protected $primaryKey = 'user_id';
    public $timestamps = true;


    protected $hidden = [
        'password', 'remember_token',
    ];

    /* Get Detail */
    public function get_detail($wil_id)
    {
        $rs = DB::table("$this->table as a")
            ->select('a.*', 'b.*', 'c.*')
            ->join('pengurus as b', 'b.warga_id', '=', 'a.user_ref_id')
            ->join('masa_kepengurusan as c', 'c.mk_id', '=', 'b.mk_id')
            ->where('c.wil_id', $wil_id)
            ->where('c.mk_status', 1)
            ->get();

        if (!empty($rs))
            return $rs;
        else
            return "";
    }

    /* Get Detail */
    public function get_detail_warga($warga_id)
    {
        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.user_ref_id', $warga_id)
            ->first();

        if (!empty($rs))
            return $rs;
        else
            return "";
    }

    /* Get Email */
    public function get_email($email)
    {
        $rs = DB::table("$this->table as a")
            ->select('a.*')
            ->where('a.user_email', $email)
            ->first();

        if (!empty($rs))
            return $rs;
        else
            return "";
    }
}
