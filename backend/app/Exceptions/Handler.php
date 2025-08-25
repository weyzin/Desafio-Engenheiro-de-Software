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

        $reply = function (int $status, string $code, string $message, array $extra = []) use ($rid) {
            $payload = array_merge(['code' => $code, 'message' => $message], $extra);
            $resp = response()->json($payload, $status);
            if ($rid) $resp->headers->set('X-Request-Id', $rid);
            return $resp;
        };

        // Mapeamento amigável
        if ($e instanceof AuthenticationException) {
            return $reply(401, 'UNAUTHENTICATED', 'Não autenticado.');
        }

        if ($e instanceof AuthorizationException) {
            return $reply(403, 'FORBIDDEN', 'Acesso negado.');
        }

        if ($e instanceof ValidationException) {
            return $reply(422, 'VALIDATION_ERROR', 'Erro de validação.', [
                'errors' => $e->errors(),
            ]);
        }

        if ($e instanceof TokenMismatchException) {
            return $reply(419, 'CSRF_TOKEN_MISMATCH', 'Token CSRF inválido ou ausente.');
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return $reply(404, 'NOT_FOUND', 'Recurso não encontrado.');
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $reply(405, 'METHOD_NOT_ALLOWED', 'Método não permitido.');
        }

        if ($e instanceof ThrottleRequestsException) {
            return $reply(429, 'TOO_MANY_REQUESTS', 'Muitas requisições.');
        }

        if ($e instanceof BadRequestHttpException) {
            $msg = $e->getMessage() ?: 'Requisição inválida.';
            $code = $msg === 'TENANT_HEADER_REQUIRED' ? 'TENANT_HEADER_REQUIRED' : 'BAD_REQUEST';
            return $reply(400, $code, $msg);
        }

        // Fallback
        return $reply(500, 'INTERNAL_SERVER_ERROR', 'Erro inesperado. Tente novamente mais tarde.');
    }
}
