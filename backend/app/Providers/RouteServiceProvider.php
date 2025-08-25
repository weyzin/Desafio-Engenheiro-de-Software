<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // Throttle específico para /auth/*
        RateLimiter::for('auth', function (Request $request) {
            $tenant = app(\App\Support\Tenancy\TenantManager::class)->current()?->id ?? 'no-tenant';
            $uid    = $request->user()?->id ?? 'guest';
            return Limit::perMinute(20)
                ->by($tenant.'|'.$uid.'|'.$request->ip())
                ->response(function () {
                    return response()->json([
                        'code'    => 'RATE_LIMIT_EXCEEDED',
                        'message' => 'Muitas requisições. Tente novamente mais tarde.',
                    ], 429)->header('Retry-After', 60);
                });
        });

        // Throttle padrão autenticadas
        RateLimiter::for('api', function (Request $request) {
            $tenant = app(\App\Support\Tenancy\TenantManager::class)->current()?->id ?? 'no-tenant';
            $uid    = $request->user()?->id ?? 'guest';
            return Limit::perMinute(60)
                ->by($tenant.'|'.$uid.'|'.$request->ip())
                ->response(function () {
                    return response()->json([
                        'code'    => 'RATE_LIMIT_EXCEEDED',
                        'message' => 'Muitas requisições. Tente novamente mais tarde.',
                    ], 429)->header('Retry-After', 60);
                });
        });
    }
}
