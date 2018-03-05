<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator; 
use Helper;

class UserController extends Controller
{ 
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */

    public function login(){

        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){

            $user = Auth::user();

            $success['token'] =  $user->createToken(\Config('app.name'))->accessToken;

            return response()->json(['success' => $success], \Config::get('constants.status.success'));

        }

        else{

            return response()->json(['error'=>'Unauthorised'], \Config::get('constants.status.unauthorized'));

        }

    }


    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */

    public function register(Request $request)

    { 
        $validator = Validator::make($request->all(), [

            'name' => 'required',

            'email' => 'required|email',

            'password' => 'required' 

        ]);


        if ($validator->fails()) {

            return response()->json(['error'=>$validator->errors()], \Config::get('constants.status.unauthorized'));            

        }


        $input = $request->all();

        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);

        $success['token'] =  $user->createToken(\Config('app.name'))->accessToken;

        $success['name'] =  $user->name;


        return response()->json(['success'=>$success], \Config::get('constants.status.success'));

    }


    /**
     * details api 
     * @return \Illuminate\Http\Response
     */

    public function details()
    {

        $user = Auth::user();

        return response()->json(['message' => 'success',
            'data' => $user], \Config::get('constants.status.success'));

    }
}
