<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Validator;
use App\Models\Service;
use App\Models\User;

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
            $service = Service::find($request->service_id);

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'El servei seleccionat no existeix.',
                ], 404);
            }

            $serviceEstimation = $service->estimation;

            $startTime = new \DateTime($request->hour);
            $endTime = clone $startTime;
            $endTime->modify('+' . $serviceEstimation . ' minutes');

            $reservations = Reservation::where('date', $request->date)
                ->where('worker_dni', $request->worker_dni)
                ->get();

            foreach ($reservations as $reservation) {
                $reservationStartTime = new \DateTime($reservation->hour);
                $reservationEndTime = clone $reservationStartTime;
                $reservationService = Service::find($reservation->service_id);
                $reservationEndTime->modify('+' . $reservationService->estimation . ' minutes');

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

    public function getAvailableWorkers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'hour' => 'required|date_format:H:i',
            'service_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $service = Service::find($request->service_id);

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'El servei seleccionat no existeix.',
                ], 404);
            }

            $startTime = new \DateTime($request->date . ' ' . $request->hour);
            $endTime = clone $startTime;
            $endTime->modify('+' . $service->estimation . ' minutes');

            $unavailableWorkers = Reservation::where('date', $request->date)
                ->whereIn('status', ['pending', 'completed'])
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('hour', [$startTime->format('H:i'), $endTime->format('H:i')])
                        ->orWhereRaw('? BETWEEN hour AND DATE_ADD(hour, INTERVAL ? MINUTE)', [
                            $startTime->format('H:i'),
                            $endTime->format('H:i'),
                        ]);
                })
                ->pluck('worker_dni');

            $availableWorkers = User::whereNotIn('dni', $unavailableWorkers)->get();

            return response()->json([
                'status' => true,
                'workers' => $availableWorkers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al obtenir els treballadors disponibles.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getReservationsByClient($dni)
    {
        try {
            $reservations = Reservation::where('client_dni', $dni)->get();

            if ($reservations->isEmpty()) {
                return response()->json([
                    'message' => 'No s\'han trobat reserves per aquest client.'
                ], 200);
            }

            return response()->json([
                'status' =>  true,


                'reservations' => $reservations
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Hi ha hagut un problema en carregar les reserves.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $reservation = Reservation::findOrFail($id);
            $reservation->status = $request->status;
            $reservation->save();

            return response()->json([
                'status' => true,
                'reservation' => $reservation,
                'message' => 'Estat de la reserva actualitzat correctament',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al actualitzar l\'estat de la reserva',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
