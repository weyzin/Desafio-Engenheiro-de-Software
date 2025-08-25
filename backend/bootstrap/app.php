<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;

use App\Http\Middleware\RequestId;
use App\Http\Middleware\TenantResolver;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'request.id'   => RequestId::class,
            'tenant'       => TenantResolver::class,
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // CORS global
        $middleware->append(HandleCors::class);

        // Sessão/cookies para API (sem CSRF), p/ emitir Set-Cookie no login
        $middleware->appendToGroup('api', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 422
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'code'    => 'VALIDATION_ERROR',
                'message' => 'Campos inválidos.',
                'errors'  => $e->errors(),
            ], 422);
        });

        // 401
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'code'    => 'UNAUTHENTICATED',
                'message' => 'Sessão inválida ou token ausente/expirado.',
            ], 401);
        });

        // 403
        $exceptions->render(function (AuthorizationException $e) {
            return response()->json([
                'code'    => 'FORBIDDEN',
                'message' => 'Acesso negado.',
            ], 403);
        });

        // 400 (inclui TENANT_HEADER_REQUIRED)
        $exceptions->render(function (BadRequestHttpException $e) {
            $msg  = $e->getMessage() ?: '';
            $code = $msg === 'TENANT_HEADER_REQUIRED' ? 'TENANT_HEADER_REQUIRED' : 'BAD_REQUEST';
            $out  = [
                'code'    => $code,
                'message' => $msg === 'TENANT_HEADER_REQUIRED'
                    ? 'X-Tenant ausente em ambiente de desenvolvimento.'
                    : 'Requisição malformada ou parâmetros inválidos.',
            ];
            return response()->json($out, 400);
        });

        // 404
        $exceptions->render(function (NotFoundHttpException|ModelNotFoundException $e) {
            return response()->json([
                'code'    => 'NOT_FOUND',
                'message' => 'Recurso não encontrado.',
            ], 404);
        });

        // 429
        $exceptions->render(function (ThrottleRequestsException $e) {
            $retry = (string)($e->getHeaders()['Retry-After'] ?? 60);
            return response()->json([
                'code'    => 'RATE_LIMIT_EXCEEDED',
                'message' => 'Muitas requisições. Tente novamente mais tarde.',
            ], 429)->header('Retry-After', $retry);
        });

        // 500 padrão (apenas quando não for HttpException já tratada acima)
        $exceptions->render(function (Throwable $e) {
            if ($e instanceof HttpExceptionInterface) {
                return null; // deixa o handler interno do HttpException cuidar (ou outro render acima)
            }
            return response()->json([
                'code'    => 'INTERNAL_SERVER_ERROR',
                'message' => 'Erro inesperado. Tente novamente mais tarde.',
            ], 500);
        });
    })
    ->create();
