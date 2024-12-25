<?php

namespace App\Http\Controllers;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $clients = Client::all();
            return response()->json([
                'status' => 'true',
                'clients' => $clients,
                'message' => 'Llista de clients'
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ],500);
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
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|max:9',
            'name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'telf' => 'required|string|max:20',
            'email' => 'required|string|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
            ], 422);
        }

        try {
            $clients = Client::create($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Client creat amb exit'
            ],201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error al afegir el client',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $client = Client::findOrFail($id);
            return response()->json([
                'status' => true,
                'client' => $client,
                'message' => 'Client trobat'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Client no trobat'
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
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|max:9',
            'name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'telf' => 'required|string|max:20',
            'email' => 'required|string|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
            ], 422);
        }

        try {
            $client = Client::findOrFail($id);
            $client->update($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Client actualitzat amb exit'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al actualitzar el client',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $dni)
    {
        try {
            $client = Client::where('dni', $dni)->firstOrFail();
            $client->delete();
            return response()->json([
                'status' => true,
                'message' => 'Client eliminat amb exit'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar el client',
            ], 500);
        }
    }
}
