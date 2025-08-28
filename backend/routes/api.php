<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;

Route::prefix('v1')
    ->middleware(['api', 'request.id'])
    ->group(function () {

        // --- públicos (sem tenant) ---
        Route::post('auth/login',  [AuthController::class, 'login']);
        Route::post('auth/forgot', [AuthController::class, 'forgot'])->middleware('throttle:5,1');
        Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

        // --- autenticados, mas ainda SEM tenant ---
        // /me não precisa de tenant (superuser global, ou para descobrir o active_tenant)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('auth/logout', [AuthController::class, 'logout']);
            Route::get('me',           [AuthController::class, 'me']);

            // ===== Admin global (somente superuser via policies) =====
            // Tenants
            Route::get   ('tenants',        [TenantController::class, 'index']);
            Route::post  ('tenants',        [TenantController::class, 'store']);
            Route::get   ('tenants/{id}',   [TenantController::class, 'show']);
            Route::put   ('tenants/{id}',   [TenantController::class, 'update']);
            Route::delete('tenants/{id}',   [TenantController::class, 'destroy']);

            // Users
            Route::get   ('users',          [UserController::class, 'index']);
            Route::post  ('users',          [UserController::class, 'store']);
            Route::get   ('users/{id}',     [UserController::class, 'show']);
            Route::put   ('users/{id}',     [UserController::class, 'update']);
            Route::delete('users/{id}',     [UserController::class, 'destroy']);
        });

        // --- autenticados + COM tenant resolvido ---
        Route::middleware(['tenant', 'auth:sanctum'])->group(function () {
            // vehicles
            Route::get   ('vehicles',         [VehicleController::class, 'index']);
            Route::post  ('vehicles',         [VehicleController::class, 'store']);
            Route::get   ('vehicles/{id}',    [VehicleController::class, 'show']);
            Route::put   ('vehicles/{id}',    [VehicleController::class, 'update']);
            Route::delete('vehicles/{id}',    [VehicleController::class, 'destroy']);
        });
    });
