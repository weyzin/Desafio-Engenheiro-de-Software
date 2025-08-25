<?php

return [
    // Permitir resolver tenant via header X-Tenant
    // Mantém true em CLI/testing para os testes funcionarem sem domínio.
    'allow_header' => env('TENANCY_ALLOW_HEADER', true),
];
