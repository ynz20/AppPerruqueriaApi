<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $this->checkUserAuth();
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|max:20|unique:users',
            'name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'nick' => 'required|string|max:50|unique:users',
            'telf' => 'required|string|max:20',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|confirmed|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ],422);
        }

        $user = User::create([
            'dni' => $request->dni,
            'name' => $request->name,
            'surname' => $request->surname,
            'nick' => $request->nick,
            'telf' => $request->telf,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => 0,
        ]);

        Auth::login($user);
        $token = $user->createToken('Api-Token')->plainTextToken;
        return response()->json([
            'status'=>true,
            'user'=> $user,
            'message'=>"Usuari registrat correctament",
            'token'=>$token,
        ],200);
    }

    
}
