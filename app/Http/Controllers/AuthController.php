<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"auth"},
     *     summary="Register a new user",
     *     description="Creates a new user account and returns an API token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="dni", type="string", example="12345678X"),
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="surname", type="string", example="Doe"),
     *             @OA\Property(property="nick", type="string", example="johndoe"),
     *             @OA\Property(property="telf", type="string", example="123456789"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", example="StrongPassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="StrongPassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="Usuari registrat correctament"),
     *             @OA\Property(property="token", type="string", example="api_token_string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"auth"},
     *     summary="User login",
     *     description="Authenticates a user using email or nickname and password.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="nick", type="string", example="johndoe"),
     *             @OA\Property(property="password", type="string", example="StrongPassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="api_token_string"),
     *             @OA\Property(property="message", type="string", example="Benvingut"),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="role", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Credencials Incorrectes")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"auth"},
     *     summary="User logout",
     *     description="Logs out the currently authenticated user.",
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
