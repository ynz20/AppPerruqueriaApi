<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Validator;
use App\Models\Service;
use App\Models\User;
use App\Models\Shift;

/**
 * @OA\Schema(
 *     schema="Reservation",
 *     type="object",
 *     title="Reservation",
 *     description="Reservation model schema",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="date", type="string", format="date", example="2023-01-01"),
 *     @OA\Property(property="hour", type="string", example="14:00"),
 *     @OA\Property(property="worker_dni", type="string", example="12345678X"),
 *     @OA\Property(property="client_dni", type="string", example="87654321X"),
 *     @OA\Property(property="service_id", type="integer", example=1),
 *     @OA\Property(property="shift_id", type="integer", example=2),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="rating", type="integer", example=4),
 *     @OA\Property(property="comment", type="string", example="Great service!"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00Z")
 * )
 */
class ReservationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/reservations",
     *     tags={"Reservations"},
     *     summary="Retrieve all reservations",
     *     description="Returns a list of all reservations",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Reservation")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/reservations",
     *     tags={"Reservations"},
     *     summary="Create a new reservation",
     *     description="Adds a new reservation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reservation created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'hour' => 'required|string|max:8',
            'worker_dni' => 'required|string|max:10',
            'client_dni' => 'required|string|max:10',
            'service_id' => 'required|integer',
            'shift_id' => 'nullable|integer|exists:shifts,id',
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
     * @OA\Get(
     *     path="/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Retrieve a specific reservation",
     *     description="Fetches a reservation by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the reservation to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation found successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="reservation", ref="#/components/schemas/Reservation"),
     *             @OA\Property(property="message", type="string", example="Reserva trobada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Reservation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Reserva no trobada")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Update a reservation",
     *     description="Updates an existing reservation with the provided data.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the reservation to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="date", type="string", format="date", example="2023-01-01", description="Date of the reservation"),
     *             @OA\Property(property="hour", type="string", example="14:00", description="Starting hour of the reservation in HH:mm format"),
     *             @OA\Property(property="worker_dni", type="string", example="12345678X", description="DNI of the worker handling the reservation"),
     *             @OA\Property(property="client_dni", type="string", example="87654321X", description="DNI of the client making the reservation"),
     *             @OA\Property(property="service_id", type="integer", example=1, description="ID of the service being reserved"),
     *             @OA\Property(property="shift_id", type="integer", example=2, description="Shift ID associated with the reservation"),
     *             @OA\Property(property="status", type="string", example="pending", description="Status of the reservation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="reservation", ref="#/components/schemas/Reservation"),
     *             @OA\Property(property="message", type="string", example="Reserva actualitzada correctament")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en la validació de dades")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating reservation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al actualitzar la reserva")
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Delete a reservation",
     *     description="Deletes a reservation by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the reservation to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reserva eliminada correctament")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error deleting reservation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al eliminar la reserva")
     *         )
     *     )
     * )
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

    /**
     * @OA\Get(
     *     path="/reservations/available-workers",
     *     tags={"Reservations"},
     *     summary="Get available workers for a reservation",
     *     description="Returns a list of workers available for a given date, time, and service.",
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Date of the reservation",
     *         required=true,
     *         @OA\Schema(type="string", format="date", example="2023-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="hour",
     *         in="query",
     *         description="Hour of the reservation in HH:mm format",
     *         required=true,
     *         @OA\Schema(type="string", format="time", example="14:00")
     *     ),
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="ID of the service for which to find available workers",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of available workers retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="workers",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El servei seleccionat no existeix.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving available workers",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtenir els treballadors disponibles."),
     *             @OA\Property(property="error", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */

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


    /**
     * @OA\Get(
     *     path="/reservations/client/{dni}",
     *     tags={"Reservations"},
     *     summary="Get reservations by client",
     *     description="Returns all reservations associated with a specific client based on their DNI.",
     *     @OA\Parameter(
     *         name="dni",
     *         in="path",
     *         description="DNI of the client",
     *         required=true,
     *         @OA\Schema(type="string", example="87654321X")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of reservations for the client retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="reservations",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Reservation")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No reservations found for the client",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No s'han trobat reserves per aquest client.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving reservations for the client",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Hi ha hagut un problema en carregar les reserves."),
     *             @OA\Property(property="error", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */

    public function getReservationsByClient($dni)
    {
        try {
            $reservations = Reservation::where('client_dni', $dni)
                ->with(['client', 'service'])
                ->get();

            if ($reservations->isEmpty()) {
                return response()->json([
                    'message' => 'No s\'han trobat reserves per aquest client.'
                ], 200);
            }

            return response()->json([
                'status' => true,
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


    /**
     * @OA\Get(
     *     path="/reservations/worker/{dni}",
     *     tags={"Reservations"},
     *     summary="Get reservations by worker",
     *     description="Returns all reservations assigned to a specific worker based on their DNI.",
     *     @OA\Parameter(
     *         name="dni",
     *         in="path",
     *         description="DNI of the worker",
     *         required=true,
     *         @OA\Schema(type="string", example="12345678X")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of reservations for the worker retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="reservations",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Reservation")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No reservations found for the worker",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No s'han trobat reserves per aquest treballador.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving reservations for the worker",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Hi ha hagut un problema en carregar les reserves."),
     *             @OA\Property(property="error", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */

    public function getReservationsByWorker($dni)
    {
        try {
            $reservations = Reservation::where('worker_dni', $dni)
                ->with(['user', 'client', 'service'])
                ->get();;

            if ($reservations->isEmpty()) {
                return response()->json([
                    'message' => 'No s\'han trobat reserves per aquest treballador.'
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

    /**
     * @OA\Put(
     *     path="/reservations/{id}/status",
     *     tags={"Reservations"},
     *     summary="Update reservation status",
     *     description="Updates the status of a reservation. If the status is 'completed', it assigns an open shift if available.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the reservation to update",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="completed", description="New status of the reservation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="reservation", ref="#/components/schemas/Reservation"),
     *             @OA\Property(property="message", type="string", example="Estat de la reserva actualitzat correctament")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No open shift available for the reservation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No hi ha cap torn obert per assignar a aquesta reserva.")
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
     *         description="Error updating reservation status",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al actualitzar l'estat de la reserva"),
     *             @OA\Property(property="error", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */

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
            if ($request->status === 'completed') {
                // Buscar el turno que tenga end_time como null
                $shift = Shift::whereNull('end_time')->first();

                if ($shift) {
                    // Asignar el turno a la reserva
                    $reservation->shift_id = $shift->id;
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No hi ha cap torn obert per assignar a aquesta reserva.',
                    ], 400);
                }
            }

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


    /**
     * @OA\Post(
     *     path="/reservations/{id}/rate",
     *     tags={"Reservations"},
     *     summary="Rate a reservation",
     *     description="Adds a rating and an optional comment to a reservation.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the reservation to rate",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="rating", type="integer", example=5, description="Rating value between 1 and 5"),
     *             @OA\Property(property="comment", type="string", example="Great service!", description="Optional comment for the reservation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation rated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="reservation", ref="#/components/schemas/Reservation"),
     *             @OA\Property(property="message", type="string", example="Valoració afegida correctament")
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
     *         description="Error adding the rating",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al afegir la valoració"),
     *             @OA\Property(property="error", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */

    public function rateReservation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
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


            $reservation->rating = $request->rating;
            $reservation->comment = $request->comment;
            $reservation->save();

            return response()->json([
                'status' => true,
                'reservation' => $reservation,
                'message' => 'Valoració afegida correctament',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al afegir la valoració',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
