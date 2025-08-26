<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

    protected function wantsJson($request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    public function render($request, Throwable $e)
    {
        if (! $this->wantsJson($request)) {
            return parent::render($request, $e);
        }

        $rid = $request->headers->get('X-Request-Id') ?? $request->attributes->get('request_id');

        $reply = function (int $status, string $code, string $message, array $extra = [], array $headers = []) use ($rid) {
            $payload = array_merge(['code' => $code, 'message' => $message], $extra);
            $resp = response()->json($payload, $status);
            if ($rid) {
                $resp->headers->set('X-Request-Id', $rid);
            }
            foreach ($headers as $k => $v) {
                $resp->headers->set($k, $v);
            }
            return $resp;
        };

        // 401 - não autenticado
        if ($e instanceof AuthenticationException) {
            return $reply(401, 'UNAUTHENTICATED', 'Não autenticado.');
        }

        // 403 - sem permissão (Policy/Authorization)
        if ($e instanceof AuthorizationException) {
            return $reply(403, 'ACCESS_DENIED', 'Ação não permitida.');
        }

        // 422 - validação
        if ($e instanceof ValidationException) {
            return $reply(422, 'VALIDATION_ERROR', 'Erro de validação.', [
                'errors' => $e->errors(),
            ]);
        }

        // 419 - CSRF
        if ($e instanceof TokenMismatchException) {
            return $reply(419, 'CSRF_TOKEN_MISMATCH', 'Token CSRF inválido ou ausente.');
        }

        // 404 - não encontrado (inclui ModelNotFound + rota inexistente)
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return $reply(404, 'NOT_FOUND', 'Recurso não encontrado.');
        }

        // 405 - método não permitido
        if ($e instanceof MethodNotAllowedHttpException) {
            return $reply(405, 'METHOD_NOT_ALLOWED', 'Método não permitido.');
        }

        // 429 - limite de requisições (propaga Retry-After se existir)
        if ($e instanceof ThrottleRequestsException) {
            $headers = method_exists($e, 'getHeaders') ? ($e->getHeaders() ?? []) : [];
            return $reply(429, 'TOO_MANY_REQUESTS', 'Muitas requisições.', [], $headers);
        }

        // 400 - bad request (inclui nosso TENANT_HEADER_REQUIRED)
        if ($e instanceof BadRequestHttpException) {
            $msg  = $e->getMessage() ?: 'Requisição inválida.';
            $code = $msg === 'TENANT_HEADER_REQUIRED' ? 'TENANT_HEADER_REQUIRED' : 'BAD_REQUEST';
            return $reply(400, $code, $msg);
        }

        // Demais HttpExceptions (ex.: 503 etc.) – mantém status e mensagem
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $msg = $e->getMessage() ?: 'Erro HTTP.';
            return $reply($status, 'HTTP_ERROR', $msg);
        }

        // 500 - fallback
        return $reply(500, 'INTERNAL_SERVER_ERROR', 'Erro inesperado. Tente novamente mais tarde.');
    }
}
