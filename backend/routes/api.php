<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;

Route::middleware(['api', 'request.id', 'tenant'])
    ->prefix('v1')
    ->group(function () {
        // públicos
        Route::post('auth/login',  [AuthController::class, 'login']);
        Route::post('auth/forgot', [AuthController::class, 'forgot']);

        // protegidos (Sanctum token)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('auth/logout', [AuthController::class, 'logout']);

            // /me autenticado
            Route::get('me', [AuthController::class, 'me']);

            // vehicles
            Route::get('vehicles',         [VehicleController::class, 'index']);
            Route::post('vehicles',        [VehicleController::class, 'store']);
            Route::get('vehicles/{id}',    [VehicleController::class, 'show']);
            Route::put('vehicles/{id}',    [VehicleController::class, 'update']);
            Route::delete('vehicles/{id}', [VehicleController::class, 'destroy']);
        });
    });
