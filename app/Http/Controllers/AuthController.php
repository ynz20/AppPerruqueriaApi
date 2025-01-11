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
            'dni' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'nick' => 'required|string|max:50',
            'telf' => 'required|string|max:20',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8'
        ]);

        if (User::where('dni', $request->dni)->exists()) {
            return response()->json([
                'success' => false,
                'id' => 1,
                'message' => 'Aquest DNI ja existeix'
            ]);
        }

        if (User::where('nick', $request->nick)->exists()) {
            return response()->json([
                'success' => false,
                'id' => 2,
                'message' => "Aquest nom d'usuari ja existeix"
            ]);
        }

        if (User::where('email', $request->email)->exists()){
            return response()->json([
                'success' => false,
                'id' => 3,
                'message' => "Aquest email ja existeix"
            ]);
        }

        if (User::where('telf', $request->telf)->exists()) {
            return response()->json([
                'success' => false,
                'id' => 4,
                'message' => "Aquest teléfon ja existeix"
            ]);
        }
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
            'is_admin' => 1,
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

        // Validar credencials
        $credentials = $request->only(['email', 'nick', 'password']);
        $validator = Validator::make($credentials, [
            'password' => 'required|string',
            'email' => 'nullable|email',
            'nick' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Comprovar si existeix un usuari amb el *nick* o el correu electrònic
        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->email)
                ->orWhere('nick', $request->nick);
        })->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            $token = $user->createToken('Api-Token')->plainTextToken;

            return response()->json([
                'status' => true,
                'token' => $token,
                'message' => 'Benvingut',
                'user_id' => $user->id,
                'role' => $user->is_admin,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Credencials Incorrectes',
        ], 401);
    }

    


    public function logout(Request $request)

    {

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
