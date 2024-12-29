<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{

            $reservations = Reservation::with(['user', 'client', 'service'])->get();
       

            return response()->json([
                'status' => 'true',
                'reservations' => $reservations,
                'message' => 'Llista de reserves',
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
            'date' => 'required|date',
            'hour' => 'required|string|max:8',
            'worker_dni' => 'required|string|max:9',
            'client_dni' => 'required|string|max:9',
            'service_id' => 'required|integer',
            'shift_id' => 'required|integer',
            'status' => 'required|string|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
            ], 422);
        }

        try {
            $reservations = Reservation::create($request->all());
            return response()->json([
                'status' => true,
                'reservations' => $reservations,
                'message' => 'Reserva creada correctament'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al afegir la reserva',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $reservation = Reservation::find($id);
            return response()->json([
                'status' => 'true',
                'reservation' => $reservation,
                'message' => 'Reserva trobada'
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Reserva no trobada'
            ],500);
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
            'date' => 'required|date',
            'hour' => 'required|string|max:8',
            'worker_dni' => 'required|string|max:9',
            'client_dni' => 'required|string|max:9',
            'service_id' => 'required|integer',
            'shift_id' => 'required|integer',
            'status' => 'required|string|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
            ], 422);
        }

        try {
            $reservation = Reservation::findOrFail($id);
            $reservation->update($request->all());
            return response()->json([
                'status' => true,
                'reservation' => $reservation,
                'message' => 'Reserva actualitzada correctament'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al actualitzar la reserva',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            $reservation->delete();
            return response()->json([
                'status' => true,
                'message' => 'Reserva eliminada correctament'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar la reserva',
            ], 500);
        }
    }
}
