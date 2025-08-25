<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use JsonSerializable;

final class ApiResponse
{
    /**
     * Retorna { "data": ... }
     */
    public static function item(array|Arrayable|JsonSerializable $resource, int $status = 200): JsonResponse
    {
        if ($resource instanceof Arrayable) {
            $resource = $resource->toArray();
        }

        return response()->json(['data' => $resource], $status);
    }

    /**
     * Retorna { "data": [...], "meta": {...}, "links": {...} }
     */
    public static function paginated(LengthAwarePaginator|Paginator $paginator, int $status = 200): JsonResponse
    {
        $collection = $paginator->getCollection();
        $data = $collection instanceof Arrayable ? $collection->toArray() : $collection->all();

        $payload = [
            'data' => $data,
            'meta' => [
                'current_page' => method_exists($paginator, 'currentPage') ? $paginator->currentPage() : null,
                'from'        => $paginator->firstItem(),
                'last_page'   => method_exists($paginator, 'lastPage') ? $paginator->lastPage() : null,
                'path'        => $paginator->path(),
                'per_page'    => $paginator->perPage(),
                'to'          => $paginator->lastItem(),
                'total'       => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => method_exists($paginator, 'lastPage') ? $paginator->url($paginator->lastPage()) : null,
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ];

        return response()->json($payload, $status);
    }

    /**
     * Retorna erro padronizado { "code": "...", "message": "...", "errors"?: {...} }
     */
    public static function error(string $code, string $message, array $errors = [], int $status = 400): JsonResponse
    {
        $body = ['code' => $code, 'message' => $message];
        if ($errors !== []) {
            $body['errors'] = $errors;
        }
        return response()->json($body, $status);
    }
}
