<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;

Route::prefix('api/v1')->group(function () {
    // Público (sem tenancy)
    Route::get('/health', HealthController::class)
        ->middleware('request.id');

    // Authed + tenancy
    Route::middleware(['request.id','tenant'])->group(function () {
        // /auth/* tem throttle mais restrito
        Route::middleware('throttle:auth')->group(function () {
            Route::post('/auth/login',  [AuthController::class,'login']);
            Route::post('/auth/forgot', [AuthController::class,'forgot']);
            Route::post('/auth/logout', [AuthController::class,'logout'])->middleware('auth:sanctum');
        });

        // Demais rotas autenticadas
        Route::middleware(['auth:sanctum','throttle:api'])->group(function () {
            Route::get('/me', [AuthController::class,'me']);

            // Vehicles (CRUD + filtros + paginação)
            Route::get('/vehicles',          [VehicleController::class,'index']);
            Route::post('/vehicles',         [VehicleController::class,'store'])->can('create','App\Models\Vehicle');
            Route::get('/vehicles/{id}',     [VehicleController::class,'show'])->whereNumber('id');
            Route::put('/vehicles/{id}',     [VehicleController::class,'update'])->whereNumber('id');
            Route::delete('/vehicles/{id}',  [VehicleController::class,'destroy'])->whereNumber('id');
        });
    });
});
