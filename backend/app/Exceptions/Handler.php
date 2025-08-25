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
    public function register(): void
    {
        $this->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'code'    => 'VALIDATION_ERROR',
                'message' => 'Campos inválidos.',
                'errors'  => $e->errors(),
            ], 422);
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            return response()->json([
                'code'    => 'UNAUTHENTICATED',
                'message' => 'Sessão inválida ou token ausente/expirado.',
            ], 401);
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            return response()->json([
                'code'    => 'FORBIDDEN',
                'message' => 'Acesso negado.',
            ], 403);
        });

        $this->renderable(function (BadRequestHttpException $e, $request) {
            $message = $e->getMessage() ?: 'Requisição malformada ou parâmetros inválidos.';
            $code    = $message === 'TENANT_HEADER_REQUIRED' ? 'TENANT_HEADER_REQUIRED' : 'BAD_REQUEST';
            return response()->json([
                'code'    => $code,
                'message' => $message === 'TENANT_HEADER_REQUIRED'
                              ? 'X-Tenant ausente em ambiente de desenvolvimento.'
                              : 'Requisição malformada ou parâmetros inválidos.',
            ], 400);
        });

        $this->renderable(function (NotFoundHttpException|ModelNotFoundException $e, $request) {
            return response()->json([
                'code'    => 'NOT_FOUND',
                'message' => 'Recurso não encontrado.',
            ], 404);
        });

        $this->renderable(function (ThrottleRequestsException $e, $request) {
            $retry = (string)($e->getHeaders()['Retry-After'] ?? 60);
            return response()->json([
                'code'    => 'RATE_LIMIT_EXCEEDED',
                'message' => 'Muitas requisições. Tente novamente mais tarde.',
            ], 429)->header('Retry-After', $retry);
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($e instanceof HttpExceptionInterface) {
                return null; // já tratado pelos cases acima
            }
            // 500 genérico
            return response()->json([
                'code'    => 'INTERNAL_SERVER_ERROR',
                'message' => 'Erro inesperado. Tente novamente mais tarde.',
            ], 500);
        });
    }
}
