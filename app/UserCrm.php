<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Contracts\Auth\MustVerifyEmail;
//use Illuminate\Foundation\Auth\User as Authenticatable;
//use Illuminate\Notifications\Notifiable;
//use Spatie\Permission\Traits\HasRoles;
use DB;
//use Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserCrm extends Model
{
    //use Notifiable, HasRoles;
    //use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function get_user($email, $pass)
    {

        $rs = DB::table("users as a")
            ->join("model_has_roles as b", "b.model_id","=", "a.id")
            ->join("roles as c", "b.role_id","=", "c.id")
            ->select('a.id','a.email', 'a.name', 'c.id as role_id', 'c.name as role_name')
            ->where("a.email",$email)
            ->first();
        $ps = DB::table("users as a")
            ->select('a.password')
            ->where("a.email",$email)
            ->first();
        if(!empty($rs)){
            if(Hash::check($pass, $ps->password)){            

                    $response['status'] = "1";
                    $response['message'] = "sukses login";
                    $response['result'] = $rs;
                    $response['token'] = Str::random(40);
                    // return json response
                    return response()->json($response, 200);
            }else{
                    $response['status'] = "0";
                    $response['message'] = "Password Salah";
                    $response['result'] = "";
                    // return json response
                    return response()->json($response, 401);
            }
        }
        else{
            // response
            $response['status'] = "0";
            $response['message'] = "Email tidak terdaftar";
            $response['result'] = "";
            // return json response
            return response()->json($response);
        }
        
        
    }

}
