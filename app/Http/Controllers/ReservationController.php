<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Validator;
use App\Models\Service;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $reservations = Reservation::with(['user', 'client', 'service'])->get();


            return response()->json([
                'status' => 'true',
                'reservations' => $reservations,
                'message' => 'Llista de reserves',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
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
            'date' => 'required|date',
            'hour' => 'required|string|max:8',
            'worker_dni' => 'required|string|max:10',
            'client_dni' => 'required|string|max:10',
            'service_id' => 'required|integer',
            'shift_id' => 'required|integer',
            'status' => 'required|string|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Obtener la estimación del servicio
            $service = Service::find($request->service_id);

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'El servei seleccionat no existeix.',
                ], 404);
            }

            $serviceEstimation = $service->estimation; // Minutos de estimación

            // Calcular hora de inicio y fin
            $startTime = new \DateTime($request->hour);
            $endTime = clone $startTime;
            $endTime->modify('+' . $serviceEstimation . ' minutes');

            // Obtener todas las reservas del mismo día y trabajador
            $reservations = Reservation::where('date', $request->date)
                ->where('worker_dni', $request->worker_dni)
                ->get();

            // Verificar conflictos manualmente
            foreach ($reservations as $reservation) {
                $reservationStartTime = new \DateTime($reservation->hour);
                $reservationEndTime = clone $reservationStartTime;
                $reservationService = Service::find($reservation->service_id);
                $reservationEndTime->modify('+' . $reservationService->estimation . ' minutes');

                // Comprobar solapamiento
                if (
                    $startTime < $reservationEndTime &&
                    $endTime > $reservationStartTime
                ) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No es pot realitzar la reserva. La franja horària està ocupada.',
                    ], 409);
                }
            }

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
        try {
            $reservation = Reservation::find($id);
            return response()->json([
                'status' => 'true',
                'reservation' => $reservation,
                'message' => 'Reserva trobada'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Reserva no trobada'
            ], 500);
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
