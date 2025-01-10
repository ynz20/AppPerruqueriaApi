<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
