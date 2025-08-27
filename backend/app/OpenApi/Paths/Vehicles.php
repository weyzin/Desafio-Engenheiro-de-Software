<?php

namespace App\OpenApi\Paths;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *   path="/vehicles",
 *   tags={"Vehicles"},
 *   security={{"sanctum": {}}},
 *   summary="Listar veículos do tenant ativo",
 *   description="Superuser sem tenant ativo recebe lista vazia e um hint. Para ativar, envie `X-Tenant: {slug}`.",
 *   @OA\Parameter(name="X-Tenant", in="header", required=false, description="Slug do tenant (necessário p/ superuser)", @OA\Schema(type="string")),
 *   @OA\Parameter(name="brand", in="query", @OA\Schema(type="string")),
 *   @OA\Parameter(name="model", in="query", @OA\Schema(type="string")),
 *   @OA\Parameter(name="price_min", in="query", @OA\Schema(type="number", format="float")),
 *   @OA\Parameter(name="price_max", in="query", @OA\Schema(type="number", format="float")),
 *   @OA\Parameter(
 *     name="sort", in="query",
 *     description="Campos permitidos: price, year, created_at. Use `-` para desc (ex.: `-price,year`).",
 *     @OA\Schema(type="string"), example="-price,year"
 *   ),
 *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/PaginatedVehicles")),
 *   @OA\Response(response=401, description="UNAUTHENTICATED", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Post(
 *   path="/vehicles",
 *   tags={"Vehicles"},
 *   security={{"sanctum": {}}},
 *   summary="Criar veículo no tenant ativo",
 *   @OA\Parameter(name="X-Tenant", in="header", required=false, description="Slug do tenant (superuser)", @OA\Schema(type="string")),
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/VehicleStore")),
 *   @OA\Response(response=201, description="Criado", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Vehicle"))),
 *   @OA\Response(response=422, description="VALIDATION_ERROR", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Get(
 *   path="/vehicles/{id}",
 *   tags={"Vehicles"},
 *   security={{"sanctum": {}}},
 *   summary="Detalhar veículo",
 *   @OA\Parameter(name="X-Tenant", in="header", required=false, description="Slug do tenant (superuser)", @OA\Schema(type="string")),
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Vehicle")),
 *   @OA\Response(response=404, description="NOT_FOUND", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Put(
 *   path="/vehicles/{id}",
 *   tags={"Vehicles"},
 *   security={{"sanctum": {}}},
 *   summary="Atualizar veículo",
 *   @OA\Parameter(name="X-Tenant", in="header", required=false, @OA\Schema(type="string")),
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/VehicleUpdate")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Vehicle"))),
 *   @OA\Response(response=422, description="VALIDATION_ERROR", @OA\JsonContent(ref="#/components/schemas/Error")),
 *   @OA\Response(response=404, description="NOT_FOUND", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Delete(
 *   path="/vehicles/{id}",
 *   tags={"Vehicles"},
 *   security={{"sanctum": {}}},
 *   summary="Excluir veículo",
 *   @OA\Parameter(name="X-Tenant", in="header", required=false, @OA\Schema(type="string")),
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=204, description="Sem conteúdo"),
 *   @OA\Response(response=404, description="NOT_FOUND", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 */
final class Vehicles {}
