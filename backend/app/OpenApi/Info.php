<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *   @OA\Info(
 *     version="1.0.0",
 *     title="Desafio – API",
 *     description="Documentação da API (multi-tenant, usuários, veículos)."
 *   ),
 *   @OA\Server(
 *     url="http://localhost:8080/api/v1",
 *     description="Local Docker"
 *   )
 * )
 */
final class Info {}
