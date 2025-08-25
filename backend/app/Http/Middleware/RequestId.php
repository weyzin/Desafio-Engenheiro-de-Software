<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RequestId
{
    public function handle(Request $request, Closure $next)
    {
        $rid = $request->headers->get('X-Request-ID') ?: Str::uuid()->toString();
        $request->headers->set('X-Request-ID', $rid);

        // Injeta no contexto de log (JSON estruturado)
        Log::withContext(['request_id' => $rid]);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $rid);

        return $response;
    }
}
