<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     description="This is the API documentation for the authentication module.",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 */

abstract class Controller
{
    public function checkUserAuth()
    {
        if (Auth::check()) {
            return response()->json([
                'status' => true,
                'message' => 'Usuari autenticat',
            ], 200);
        }
    }
}
