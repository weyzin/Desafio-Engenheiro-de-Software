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

        // /me SEM auth (tenant valida primeiro; em dev sem X-Tenant → 400)
        Route::get('me', function (Request $request) {
            $u = $request->user(); // estará preenchido após login
            return response()->json([
                'data' => [
                    'id'        => $u->id,
                    'email'     => $u->email,
                    'tenant_id' => $u->tenant_id,
                ],
            ], 200);
        });

        // protegidos
        Route::middleware('auth')->group(function () {
            Route::post('auth/logout', [AuthController::class, 'logout']);

            Route::get('vehicles',         [VehicleController::class, 'index']);
            Route::post('vehicles',        [VehicleController::class, 'store']);
            Route::get('vehicles/{id}',    [VehicleController::class, 'show']);
            Route::put('vehicles/{id}',    [VehicleController::class, 'update']);
            Route::delete('vehicles/{id}', [VehicleController::class, 'destroy']);
        });
    });
