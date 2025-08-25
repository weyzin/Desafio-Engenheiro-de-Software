<?php

use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'request.id'   => \App\Http\Middleware\RequestId::class,
            'tenant'       => \App\Http\Middleware\TenantResolver::class,
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Garantir CORS global (opcional se já vier no preset)
        $middleware->append(HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
