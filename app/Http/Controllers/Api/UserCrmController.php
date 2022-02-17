<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\UserCrm;
use App\Providers\RouteServiceProvider;
use Response;
use Illuminate\Http\Request;
//use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class UserCrmController extends Controller
{

    //use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    //protected $redirectTo = RouteServiceProvider::HOME;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$useradmin = User::latest()->paginate(10);
        $useradmin = DB::table('users')   
        ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->leftJoin('roles','model_has_roles.role_id','=','roles.id')
        ->select('users.*', 'roles.name as rolename')
        ->paginate(5);
        return view('user.index',compact('useradmin'))->with('i', (request()->input('page', 1) - 1) * 10);
        //return view('user.index')->with('useradmin', $useradmin);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    /*public function __construct()
    {
        $this->middleware('guest');
    }*/

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
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data 
     * @return \App\User
     */
    protected function create()
    {
        return view('user.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $data)
    {
        $user =  UserCrm::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        if($data['role']==1){
            $user->assignRole('admin');
        }else{
            $user->assignRole('user');
        }

        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    public function login(Request $request, UserCrm $user)
    {
        $email =  $request->get('email');
        $password =  $request->get('password');

        $user = $user->get_user($email, $password);
        return $user;
        
        
    }
    public function logincrm(Request $request, UserCrm $user){
        $email =  $request->get('email');
        $password =  $request->get('password');
        $user =$user->get_user($email, $password);
        if (!$user) {
            return response()->json(['error' => 'Password Salah'], 401);
        }
        $token = Str::random(40);

        return $this->respondWithToken($token);
    }
    protected function respondWithToken($token)
    {
      return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => '3600'
      ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserCrm $user)
    {
        //
    }
}