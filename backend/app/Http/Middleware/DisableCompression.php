<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableCompression
{
    public function handle(Request $request, Closure $next): Response
    {
        // Mata qualquer compressão no PHP antes de tudo
        @ini_set("zlib.output_compression", "0");
        @ini_set("output_handler", "");
        while (function_exists("ob_get_level") && ob_get_level() > 0) { @ob_end_clean(); }

        /** @var Response $response */
        $response = $next($request);

        // Limpa cabeçalhos de compressão que alguém possa ter setado
        $response->headers->remove("Content-Encoding");
        $response->headers->set("Vary", "Accept-Encoding");

        // Tenta decodificar se já veio comprimido
        $body = $response->getContent();
        if (is_string($body) && strlen($body) > 2) {
            $prefix = substr($body, 0, 2);
            $decoded = false;

            // gzip (1F 8B)
            if ($prefix === "\x1f\x8b") {
                $decoded = @gzdecode($body);
            }
            // zlib/deflate (78 01 / 78 9C / 78 DA)
            elseif (in_array($prefix, ["\x78\x01","\x78\x9c","\x78\xda"], true)) {
                $decoded = @gzinflate(substr($body, 2));
            }
            // tentativa “genérica”
            else {
                $try = @gzdecode($body);
                if ($try !== false) $decoded = $try;
            }

            if ($decoded !== false && $decoded !== "") {
                $response->setContent($decoded);
                $response->headers->remove("Content-Encoding");
            }
        }

        return $response;
    }
}