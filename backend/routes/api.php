<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;

Route::prefix('v1')
    ->middleware(['api', 'request.id'])
    ->group(function () {
        // --- públicos (sem tenant) ---
        Route::post('auth/login',  [AuthController::class, 'login']);
        Route::post('auth/forgot', [AuthController::class, 'forgot']);

        // --- autenticados, mas ainda SEM tenant ---
        // /me não precisa de tenant: serve para obter o usuário/logado + active_tenant
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('auth/logout', [AuthController::class, 'logout']);
            Route::get('me',           [AuthController::class, 'me']);
        });

        // --- autenticados + COM tenant resolvido ---
        Route::middleware(['tenant','auth:sanctum'])->group(function () {
            // vehicles
            Route::get(   'vehicles',         [VehicleController::class, 'index']);
            Route::post(  'vehicles',         [VehicleController::class, 'store']);
            Route::get(   'vehicles/{id}',    [VehicleController::class, 'show']);
            Route::put(   'vehicles/{id}',    [VehicleController::class, 'update']);
            Route::delete('vehicles/{id}',    [VehicleController::class, 'destroy']);
        });
    });
