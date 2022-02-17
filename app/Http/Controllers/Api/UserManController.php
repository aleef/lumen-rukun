<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Response;
use App\UserMan;
use App\ModelRoles;
//use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserManController extends Controller
{

    
    //use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $messages = [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah pernah didaftarkan'
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $draw = $request->get('draw');
        $start = $request->get("start");
        $length = $request->get("length");
        $search = $request->get('search')['value'];

        $order =  $request->get('order');

         $col = 0;
         $dir = "desc";
         if(!empty($order)) {
             foreach($order as $o) {
                 $col = $o['column'];
                 $dir= $o['dir'];
             }
        }

         if($dir != "asc" && $dir != "desc") {
             $dir = "desc";
         }

         $columns_valid = array("id", "name", "email");
         if(!isset($columns_valid[$col])) {
            $order = null;
        } else {
            $order = $columns_valid[$col];
        }
       
        $res =   DB::table('users')   
        ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->leftJoin('roles','model_has_roles.role_id','=','roles.id')
        ->select('users.*', 'roles.name as rolename');
        if($search!=''){
            $res = $res->where('users.name','ilike',"%$search%")->orWhere('users.email', 'ilike', "%$search%");
        }
		if(isset($order)){
				$res = $res->orderBy($order, $dir);
		}else{
				$order = $res->orderBy('users.id', 'desc');
		}
		if(isset($length) || isset($start)){
				$res = $res->skip($start)->take($length);
		}
        $res = $res->get();
        $i = 1;
        //$data[] =array();
         foreach($res as $r) {
             
             $data[] = array(
                 $start + $i,
                 $r->name,
                 $r->email,
                 '<form action="sales/'.$r->id.'/destroy" method="POST"><a href="userMan/'.$r->id.'/edit" title="Edit User"><i class="fa fa-edit fa-lg text-success"></i></a> <a href="#" id="hapusdata-id="'.$r->id.'" data-nama="'.$r->name.'" title="Hapus User"><i class="fa fa-trash fa-lg text-danger"></i></a></form>'
             );
             $i++;
         }

         //total data lead
        $total_sal = UserMan::count();
        //total filtered
        if($search!=''){
             $total_fil = UserMan::where('users.name','ilike',"%$search%")->orWhere('users.email', 'ilike', "%$search%")->count();
         }else{
             $total_fil = UserMan::count();
         }

         $output = array(
             "draw" => $draw,
             "recordsTotal" => $total_sal,
             "recordsFiltered" => $total_fil,
             "data" => $data
         );
         // return json response
         return response()->json($output, 200, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    }

    
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data 
     * @return \App\User
     */
    protected function create()
    {
        return view('userMan.create');
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data 
     * @return \App\User
     */
    protected function add(Request $request)
    {
        /*$request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah pernah didaftarkan'
        ]);*/
        
        try{
        
            $user =  UserMan::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
            ]);
            $role = new ModelRoles();
            $role->role_id = $request->get('role');
            $role->model_id = $user->id;
            $role->model_type = 'App\User';
            $role->save();

             // response
             $response['status'] = "success";
             $response['message'] = "OK";
             $response['results'] = "Sukses menyimpan user baru";
             // return json response
             return response()->json($response);
        }
        catch(\Exception $e){
            // response
            $response['status'] = "error";
            $response['message'] = "error";
            $response['results'] =  $e->getMessage();
            // return json response
            return response()->json($response);
        }

    }
   
 
	/*==  Detail ==*/
	public function detail($id)
	{
		// get data
		//$info = UserMan::find($id);
		//$info .= ModelRoles::find($id);
        $info = DB::table('users as a')
        ->join('model_has_roles as b','a.id','=','b.model_id')
        ->select('a.*', 'b.role_id')
        ->where('a.id', $id)
        ->first();
        //echo $this->db->last_query();
        return response()->json($info);
        //return $info;
	}


    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserMan $user)
    {
        try{
            $uman = UserMan::find($request->id);
            $uman->name =  $request->name;
            $uman->email =  $request->email;
            if($request->get('password') !='' || $request->get('password')){
                $uman->password =  Hash::make($request->get('password'));
            }

            $uman->save();

            $role = ModelRoles::find($request->id);
            $role->role_id = $request->role;
            $role->save();
            
             // response
             $response['status'] = "success";
             $response['message'] = "OK";
             $response['results'] = "Sukses mengubah data user";
             // return json response
             return response()->json($response);
        }        
        catch(\Exception $e){
            // response
            $response['status'] = "error";
            $response['message'] = "error";
            $response['results'] =  $e->getMessage();
            // return json response
            return response()->json($response);
        }
    }

    
	/*== Delete ==*/
	public function delete(Request $request)
	{
		$id = $request->id;

		// get data
		$info = UserMan::find($id);
		// theme checking
		if(empty($info))
		{
			$response['status'] = "error";
			$response['message'] = "Informasi with ID : $id not found";
			return response()->json($response);
			exit();
		}

		try
		{
			// delete
			UserMan::find($id)->delete();
			ModelRoles::find($id)->delete();

            // response
            $response['status'] = "success";
            $response['message'] = "OK";
            $response['results'] = "Sukses mengahapus user";
    
            // return json response
            return response()->json($response);
		}
		catch(\Exception $e)
		{
			// failed
			$response['status'] = "error";
			$response['message'] = "Error, can't delete the Informasi";            
            $response['results'] =  $e->getMessage();
			return response()->json($response);
			exit();
		}
	}
}