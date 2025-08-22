<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// mata compressão e buffers antes de tudo
@ini_set('zlib.output_compression', '0');
@ini_set('output_handler', '');
if (function_exists('header_remove')) { @header_remove('Content-Encoding'); }
while (function_exists('ob_get_level') && ob_get_level() > 0) { @ob_end_clean(); }
if (!headers_sent()) { header('Content-Encoding: identity'); header('Vary: Accept-Encoding'); }

require __DIR__.'/../vendor/autoload.php';

$app    = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);

$request  = Request::capture();
$response = $kernel->handle($request);   // <-- NÃO chamar send ainda

// DESCOMPACTA se veio comprimido
$body = $response->getContent();
if (is_string($body) && strlen($body) > 2) {
    $prefix  = substr($body, 0, 2);
    $decoded = false;

    if ($prefix === "\x1f\x8b") {                 // gzip
        $decoded = @gzdecode($body);
    } elseif (in_array($prefix, ["\x78\x01","\x78\x9c","\x78\xda"], true)) { // zlib/deflate
        $decoded = @gzinflate(substr($body, 2));
    } else {
        $try = @gzdecode($body);
        if ($try !== false) $decoded = $try;
    }

    if ($decoded !== false && $decoded !== '') {
        $response->setContent($decoded);
        $response->headers->remove('Content-Encoding');
        $response->headers->set('Vary', 'Accept-Encoding');
    }
}

$response->send();                     // <-- só agora envia
$kernel->terminate($request, $response);
