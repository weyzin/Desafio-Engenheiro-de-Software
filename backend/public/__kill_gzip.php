<?php
// Desliga compressão no PHP
@ini_set('zlib.output_compression', '0');
@ini_set('output_handler', '');
if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', '1'); }

// Remove Content-Encoding (se algum pacote/middleware tiver colocado)
if (function_exists('header_remove')) { @header_remove('Content-Encoding'); }

// Zera todos os buffers já abertos
while (function_exists('ob_get_level') && ob_get_level() > 0) { @ob_end_clean(); }

// (opcional) garante um content-type legível
if (!headers_sent()) { header('Content-Type: text/html; charset=UTF-8'); }
