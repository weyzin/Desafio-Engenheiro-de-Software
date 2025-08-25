<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RequestId;
use App\Http\Middleware\TenantResolver;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases reutilizáveis nas rotas/api
        $middleware->alias([
            'request.id' => RequestId::class,
            'tenant'     => TenantResolver::class,
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // JSON por padrão nas rotas da API (opcional)
        // $middleware->append(App\Http\Middleware\ForceJson::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handler padrão já cuidará dos mapeamentos;
        // detalhes serão tratados no Handler (padrão de erros do catálogo).
    })->create();
