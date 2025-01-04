<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//Rutes Públiques
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Ruta per consultar els usuaris
    Route::apiResource('users', UserController::class);
    // Ruta per consultar els clients
    Route::apiResource('clients', ClientController::class);
    // Ruta per consultar els torns
    Route::apiResource('shifts', ShiftController::class);
    //Ruta per consultar els serveis
    Route::apiResource('services', ServiceController::class);
    //ruta per consultar les reserves
    Route::apiResource('reservations', ReservationController::class);

     // Ruta per obtenir els treballadors disponibles
     Route::post('/workers/available', [ReservationController::class, 'getAvailableWorkers'])->name('workers.available');

});


