<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            
            $shifts = Shift::with([
                'reservations.service', 
                'reservations.client', 
                'reservations.user' 
            ])
            ->orderBy('id', 'desc')
            ->get();
    
            return response()->json([
                'status' => 'true',
                'shifts' => $shifts,
                'message' => 'Llista de torns'
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
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'nullable|date',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
            ], 422);
        }

        try {
            $shift = Shift::create($request->all());
            return response()->json([
                'status' => true,
                'shift' => $shift,
                'message' => 'Torn creat correctament'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $shift = Shift::find($id);
            return response()->json([
                'status' => 'true',
                'shift' => $shift,
                'message' => 'Torn trobat'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Torn no trobat'
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
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
            ], 422);
        }

        try {
            $shift = Shift::find($id);
            $shift->update($request->all());
            return response()->json([
                'status' => true,
                'shift' => $shift,
                'message' => 'Torn actualitzat correctament'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error al actualitzar el torn'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $shift = Shift::find($id);
            $shift->delete();
            return response()->json([
                'status' => true,
                'message' => 'Torn eliminat correctament'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar el torn'
            ], 500);
        }
    }

    public function toggleTurn(Request $request)
    {
        try {
            // Buscar si hi ha un torn actiu (end_time NULL)
            $activeShift = Shift::whereNull('end_time')->first();

            if ($activeShift) {
                // Si hi ha un torn actiu, tancar-lo
                $activeShift->update([
                    'end_time' => now(), // Hora actual per finalitzar el torn
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Torn tancat correctament',
                    'shift' => $activeShift,
                ], 200);
            } else {
                // Si no hi ha torn actiu, invocar el mètode store per crear-ne un
                $request->merge([
                    'start_time' => now(),
                    'end_time' => null,
                    'date' => now()->toDateString(),
                ]);

                // Cridar al mètode store
                return $this->store($request);
            }
        } catch (\Throwable $th) {
            // Gestió d'errors
            return response()->json([
                'status' => false,
                'message' => 'Error en gestionar el torn: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function getTurnStatus()
    {
        try {
            $activeShift = Shift::whereNull('end_time')->exists();

            return response()->json([
                'status' => true,
                'active' => $activeShift, // Retorna true si hi ha un torn actiu
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error en obtenir l\'estat del torn: ' . $th->getMessage(),
            ], 500);
        }
    }
}
