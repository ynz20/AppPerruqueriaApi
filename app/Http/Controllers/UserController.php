<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Retrieve all users",
     *     description="Returns a list of all users.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
     *             ),
     *             @OA\Property(property="message", type="string", example="Llista d'usuaris")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving users",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */

    public function index()
    {
        try {
            $users = User::all();
            return response()->json([
                'status' => 'true',
                'users' => $users,
                'message' => 'Llista d\'usuaris'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Retrieve a specific user",
     *     description="Fetches a user by their ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="Usuari trobat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Usuari no trobat")
     *         )
     *     )
     * )
     */

    public function show(string $id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json([
                'status' => 'true',
                'user' => $user,
                'message' => 'Usuari trobat'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Usuari no trobat'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/users/{dni}",
     *     tags={"Users"},
     *     summary="Update a user",
     *     description="Updates an existing user with the provided data.",
     *     @OA\Parameter(
     *         name="dni",
     *         in="path",
     *         description="DNI of the user to update",
     *         required=true,
     *         @OA\Schema(type="string", example="12345678X")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John", description="First name of the user"),
     *             @OA\Property(property="surname", type="string", example="Doe", description="Last name of the user"),
     *             @OA\Property(property="nick", type="string", example="johndoe", description="Nickname of the user"),
     *             @OA\Property(property="telf", type="string", example="123456789", description="Telephone number of the user"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com", description="Email address of the user"),
     *             @OA\Property(property="password", type="string", example="StrongPassword123", description="Password of the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="Usuari actualitzat correctament")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en la validació de dades"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating user",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al actualitzar l'usuari")
     *         )
     *     )
     * )
     */

    public function update(Request $request, string $dni)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'nick' => 'required|string|max:100',
            'telf' => 'required|string|max:20',
            'surname' => 'required|string|max:100',
            'email' => 'required|string|max:100|email',
            'password' => 'string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::where('dni', $dni)->firstOrFail();
            $user->update($request->all());
            return response()->json([
                'status' => true,
                'data' => $user,
                'message' => 'Usuari actualitzat correctament'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error al actualitzar l\'usuari'
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/users/workers",
     *     tags={"Users"},
     *     summary="Get all users (workers)",
     *     description="Returns a list of all users in the system.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
     *             ),
     *             @OA\Property(property="message", type="string", example="Llista d'usuaris")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving users",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */
    public function getWorkers()
    {
        try {
            $users = User::all();
            return response()->json([
                'status' => 'true',
                'users' => $users,
                'message' => 'Llista d\'usuaris'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
