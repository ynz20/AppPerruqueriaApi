<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Schema(
 *     schema="Shift",
 *     type="object",
 *     title="Shift",
 *     description="Shift model schema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID of the shift"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2023-01-01T08:00:00Z", description="Start time of the shift"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2023-01-01T16:00:00Z", description="End time of the shift"),
 *     @OA\Property(property="date", type="string", format="date", example="2023-01-01", description="Date of the shift"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z", description="Timestamp when the shift was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00Z", description="Timestamp when the shift was last updated")
 * )
 */

class ShiftController extends Controller
{
    /**
     * @OA\Get(
     *     path="/shifts",
     *     tags={"Shifts"},
     *     summary="Retrieve all shifts",
     *     description="Returns a list of all shifts with their related reservations, services, clients, and users.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="shifts",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Shift")
     *             ),
     *             @OA\Property(property="message", type="string", example="Llista de torns")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving shifts",
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
     * @OA\Post(
     *     path="/shifts",
     *     tags={"Shifts"},
     *     summary="Create a new shift",
     *     description="Adds a new shift to the system.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2023-01-01T08:00:00Z", description="Start time of the shift"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2023-01-01T16:00:00Z", description="End time of the shift (nullable)"),
     *             @OA\Property(property="date", type="string", format="date", example="2023-01-01", description="Date of the shift")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Shift created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="shift", ref="#/components/schemas/Shift"),
     *             @OA\Property(property="message", type="string", example="Torn creat correctament")
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
     *         description="Error creating shift",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Detailed error message")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/shifts/{id}",
     *     tags={"Shifts"},
     *     summary="Retrieve a specific shift",
     *     description="Fetches a shift by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the shift to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shift retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="shift", ref="#/components/schemas/Shift"),
     *             @OA\Property(property="message", type="string", example="Torn trobat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shift not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Torn no trobat")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/shifts/{id}",
     *     tags={"Shifts"},
     *     summary="Update a shift",
     *     description="Updates an existing shift with the provided data.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the shift to update",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2023-01-01T08:00:00Z", description="Start time of the shift"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2023-01-01T16:00:00Z", description="End time of the shift"),
     *             @OA\Property(property="date", type="string", format="date", example="2023-01-01", description="Date of the shift")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shift updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="shift", ref="#/components/schemas/Shift"),
     *             @OA\Property(property="message", type="string", example="Torn actualitzat correctament")
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
     *         description="Error updating shift",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Detailed error message")
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/shifts/{id}",
     *     tags={"Shifts"},
     *     summary="Delete a shift",
     *     description="Deletes a shift by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the shift to delete",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shift deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Torn eliminat correctament")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error deleting shift",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Detailed error message")
     *         )
     *     )
     * )
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

    /**
     * @OA\Post(
     *     path="/shifts/toggle",
     *     tags={"Shifts"},
     *     summary="Toggle the active shift",
     *     description="Closes the active shift if one is open, or creates a new shift if none is active.",
     *     @OA\Response(
     *         response=200,
     *         description="Shift toggled successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Torn tancat correctament"),
     *             @OA\Property(property="shift", ref="#/components/schemas/Shift")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="New shift created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="shift", ref="#/components/schemas/Shift"),
     *             @OA\Property(property="message", type="string", example="Torn creat correctament")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error toggling shift",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en gestionar el torn: Detailed error message")
     *         )
     *     )
     * )
     */

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

    /**
     * @OA\Get(
     *     path="/shifts/status",
     *     tags={"Shifts"},
     *     summary="Get the current shift status",
     *     description="Checks if there is an active shift (a shift with no end time).",
     *     @OA\Response(
     *         response=200,
     *         description="Shift status retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="active", type="boolean", example=true, description="True if there is an active shift, false otherwise")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving shift status",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en obtenir l'estat del torn: Detailed error message")
     *         )
     *     )
     * )
     */

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
