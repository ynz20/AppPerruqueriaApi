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
            'telf' => 'required|string|max:20|unique:clients,telf', 
            'email' => 'required|string|max:100|email|unique:clients,email',
        ]);

        if (Client::where('dni', $request->dni)->exists()) {
            return response()->json([
                'success' => false,
                'id' => 1,
                'message' => 'Aquest DNI ja existeix'
            ]);
        }

        if (Client::where('telf', $request->telf)->exists()) {
            return response()->json([
                'success' => false,
                'id' => 2,
                'message' => 'Aquest telèfon ja existeix'
            ]);
        }

        if (Client::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'id' => 3,
                'message' => 'Aquest email ja existeix'
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        try {
            $client = Client::create($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Client creat amb exit',
                'client' => $client,
            ], 201);
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
    public function update(Request $request, string $dni)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'telf' => 'required|string|max:20',
            'email' => 'required|string|max:100',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            $client = Client::where('dni', $dni)->firstOrFail();
    
            if (Client::where('telf', $request->telf)->where('dni', '!=', $dni)->exists()) {
                return response()->json([
                    'success' => false,
                    'id' => 1,
                    'message' => 'Aquest telèfon ja existeix'
                ]);
            }

            if (Client::where('email', $request->email)->where('dni', '!=', $dni)->exists()) {
                return response()->json([
                    'success' => false,
                    'id' => 2,
                    'message' => 'Aquest email ja existeix'
                ]);
            }

            $client->update($request->all());
    
            return response()->json([
                'status' => true,
                'client' => $client,
                'message' => 'Client actualitzat amb èxit'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al actualitzar el client: ' . $e->getMessage()
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
                'message' => 'Error al eliminar el client: ' . $e->getMessage()
            ], 500);
        }
    }
}
