<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Rutes públiques
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Ruta per a l'autenticació
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Rutes protegides per autenticació
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('shifts', ShiftController::class);
    Route::apiResource('reservations', ReservationController::class);
    Route::apiResource('products', ProductController::class);

    // Rutes per als treballadors
    Route::post('/workers/available', [ReservationController::class, 'getAvailableWorkers'])->name('workers.available');

    // Rutes per a reserves
    Route::get('reservations/client/{dni}', [ReservationController::class, "getReservationsByClient"]);
    Route::put('/reservations/{id}/status', [ReservationController::class, 'updateStatus']);
    Route::put('/reservations/{id}/rate', [ReservationController::class, 'rateReservation']);

    // Rutes de productes
    Route::post('/products/{id}/decrement-stock', [ProductController::class, 'decrementStock']);
    Route::post('/products/{id}/increment-stock', [ProductController::class, 'incrementStock']);
    Route::post('/turn', [ShiftController::class, 'toggleTurn']);
    Route::get('/turn/status', [ShiftController::class, 'getTurnStatus']);

    //Ruta per obtenir els serveis pel usuaris no admins per poder fer la reserva
    Route::get('/services/pull', [ServiceController::class, 'getServices']);

    //Ruta per obtenir els treballadors pel usuaris no admins per poder fer la reserva
    Route::get('/users/pull', [UserController::class, 'getWorkers']);

    // Permetre que qualsevol usuari accedeixi als mètodes show i update de UserController
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
});

// Rutes només per a administradors (accés restringit)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Rutes de serveis només accessibles per administradors
    Route::apiResource('services', ServiceController::class);

    // Rutes de recursos per a usuaris (exceptuant show i update)
    Route::apiResource('users', UserController::class)->except(['show', 'update']);
});
