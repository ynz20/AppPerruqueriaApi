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
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
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
            'status' => true,
            'user' => $user,
            'message' => "Usuari registrat correctament",
            'token' => $token,
        ], 200);
    }

    public function login(Request $request)
    {
        $this->checkUserAuth();
        //Valdiacio de dades
        //comprovacio del login
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('Api-Token')->plainTextToken;
            return response()->json([
                'status' => true,
                'token' => $token,
                'message' => 'Usuari loguejat',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Usuari no loguejat',
            ], 401);
        }
    }




    public function logout(Request $request)

    {

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
