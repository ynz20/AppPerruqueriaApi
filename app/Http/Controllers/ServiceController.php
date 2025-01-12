<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="Service",
 *     type="object",
 *     title="Service",
 *     description="Service model schema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID of the service"),
 *     @OA\Property(property="name", type="string", example="Haircut", description="Name of the service"),
 *     @OA\Property(property="description", type="string", example="A professional haircut service", description="Description of the service"),
 *     @OA\Property(property="price", type="number", format="float", example=25.99, description="Price of the service"),
 *     @OA\Property(property="estimation", type="integer", example=30, description="Estimated time in minutes for the service"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z", description="Timestamp when the service was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-02T00:00:00Z", description="Timestamp when the service was last updated")
 * )
 */

class ServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/services",
     *     tags={"Services"},
     *     summary="Retrieve all services",
     *     description="Returns a list of all available services.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="services",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Service")
     *             ),
     *             @OA\Property(property="message", type="string", example="Llista de serveis")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving services",
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
            $services = Service::all();
            return response()->json([
                'status' => true,
                'services' => $services,
                'message' => 'Llista de serveis'
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
     *     path="/services",
     *     tags={"Services"},
     *     summary="Create a new service",
     *     description="Adds a new service to the system.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Haircut"),
     *             @OA\Property(property="description", type="string", example="A professional haircut service."),
     *             @OA\Property(property="price", type="number", example=25.99),
     *             @OA\Property(property="estimation", type="integer", example=30)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Service"),
     *             @OA\Property(property="message", type="string", example="Servei creat correctament")
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
     *         description="Error creating service",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al crear el servei")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric',
            'estimation' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
            ], 422);
        }

        try {
            $service = Service::create($request->all());
            return response()->json([
                'status' => true,
                'data' => $service,
                'message' => 'Servei creat correctament'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error al crear el servei'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/services/{id}",
     *     tags={"Services"},
     *     summary="Retrieve a specific service",
     *     description="Fetches a service by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the service to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="service", ref="#/components/schemas/Service"),
     *             @OA\Property(property="message", type="string", example="Servei trobat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Servei no trobat")
     *         )
     *     )
     * )
     */

    public function show(string $id)
    {
        try {
            $service = Service::findOrfail($id);
            return response()->json([
                'status' => true,
                'service' => $service,
                'message' => 'Servei trobat'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => ' Servei no trobat'
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
     *     path="/services/{id}",
     *     tags={"Services"},
     *     summary="Update a service",
     *     description="Updates an existing service with the provided data.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the service to update",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Haircut Deluxe"),
     *             @OA\Property(property="description", type="string", example="An advanced haircut service."),
     *             @OA\Property(property="price", type="number", example=35.99),
     *             @OA\Property(property="estimation", type="integer", example=45)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Service"),
     *             @OA\Property(property="message", type="string", example="Servei actualitzat correctament")
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
     *         description="Error updating service",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al actualitzar el servei")
     *         )
     *     )
     * )
     */

    public function update(Request $request, string $id)
    {
        $request->merge([
            'price' => floatval($request->input('price')), // Convertir a float per poder validar
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric',
            'estimation' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
            ], 422);
        }

        try {
            $service = Service::findOrfail($id);
            $service->update($request->all());
            return response()->json([
                'status' => true,
                'data' => $service,
                'message' => 'Servei actualitzat correctament'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error al actualitzar el servei'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/services/{id}",
     *     tags={"Services"},
     *     summary="Delete a service",
     *     description="Deletes a service by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the service to delete",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Servei eliminat correctament")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error deleting service",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al eliminar el servei")
     *         )
     *     )
     * )
     */

    public function destroy(string $id)
    {
        try {
            $service = Service::findOrfail($id);
            $service->delete();
            return response()->json([
                'status' => true,
                'message' => 'Servei eliminat correctament'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar el servei'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/services/list",
     *     tags={"Services"},
     *     summary="Get all services",
     *     description="Returns a list of services with customized information.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="services",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Service")
     *             ),
     *             @OA\Property(property="message", type="string", example="Llista de serveis")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving services",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */

    public function getServices()
    {
        try {
            $services = Service::all();
            return response()->json([
                'status' => true,
                'services' => $services,
                'message' => 'Llista de serveis'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
