<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function item(array|\JsonSerializable|object $resource, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $resource], $status);
    }

    public static function collection(LengthAwarePaginator $paginator): JsonResponse
    {
        $meta = [
            'total'     => $paginator->total(),
            'page'      => $paginator->currentPage(),
            'per_page'  => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
        ];
        $links = [
            'next' => $paginator->nextPageUrl(),
            'prev' => $paginator->previousPageUrl(),
        ];
        return response()->json([
            'data'  => $paginator->items(),
            'meta'  => $meta,
            'links' => $links,
        ]);
    }
}
