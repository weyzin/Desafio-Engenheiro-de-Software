<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Garante o shape do 422 para APIs.
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'code'    => 'VALIDATION_ERROR',
            'message' => $exception->getMessage(),
            'errors'  => $exception->errors(),
        ], $exception->status);
    }

    public function register(): void
    {
        // 422 (fallback caso não passe pelo invalidJson)
        $this->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'code'    => 'VALIDATION_ERROR',
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        });

        // 401
        $this->renderable(function (AuthenticationException $e, $request) {
            return response()->json([
                'code'    => 'UNAUTHENTICATED',
                'message' => 'Sessão inválida ou token ausente/expirado.',
            ], 401);
        });

        // 403
        $this->renderable(function (AuthorizationException $e, $request) {
            return response()->json([
                'code'    => 'FORBIDDEN',
                'message' => 'Acesso negado.',
            ], 403);
        });

        // 400 (inclui TENANT_HEADER_REQUIRED)
        $this->renderable(function (BadRequestHttpException $e, $request) {
            $code    = $e->getMessage() === 'TENANT_HEADER_REQUIRED' ? 'TENANT_HEADER_REQUIRED' : 'BAD_REQUEST';
            $message = $e->getMessage() === 'TENANT_HEADER_REQUIRED'
                ? 'X-Tenant ausente em ambiente de desenvolvimento.'
                : 'Requisição malformada ou parâmetros inválidos.';
            return response()->json(['code' => $code, 'message' => $message], 400);
        });

        // 404 (inclui ModelNotFound)
        $this->renderable(function (NotFoundHttpException|ModelNotFoundException $e, $request) {
            return response()->json([
                'code'    => 'NOT_FOUND',
                'message' => 'Recurso não encontrado.',
            ], 404);
        });

        // 429
        $this->renderable(function (ThrottleRequestsException $e, $request) {
            $retry = (string)($e->getHeaders()['Retry-After'] ?? 60);
            return response()->json([
                'code'    => 'RATE_LIMIT_EXCEEDED',
                'message' => 'Muitas requisições. Tente novamente mais tarde.',
            ], 429)->header('Retry-After', $retry);
        });

        // 500
        $this->renderable(function (Throwable $e, $request) {
            if ($e instanceof HttpExceptionInterface) {
                return null;
            }
            return response()->json([
                'code'    => 'INTERNAL_SERVER_ERROR',
                'message' => 'Erro inesperado. Tente novamente mais tarde.',
            ], 500);
        });
    }
}
