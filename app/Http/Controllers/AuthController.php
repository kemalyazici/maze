<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('json');
    }

    public function register(Request $request){
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|confirmed|min:6'
        ]);
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'username' => $fields['username'],
            'password' => Hash::make($fields['password'])
        ]);


        $response = [
            'user' => $user,
        ];

        return response($response,201);
    }


    // Merchant login
    public function login(Request $request){
        $fields = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('username',$fields['username'])->first();

        // Check Password
        if(!$user || !Hash::check($fields['password'],$user->password)){
            return response(
                [
                    'message' => 'Creds are not valid',
                ],
                400);
        }
        if(!$token = auth()->attempt($fields)){
            return response(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);


    }

    protected function respondWithToken($token)
    {
        return response([
            'token' => $token,
        ],200);
    }
}
