<?php

namespace App\OpenApi\Paths;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *   path="/tenants",
 *   tags={"Tenants"},
 *   security={{"sanctum": {}}},
 *   summary="Listar tenants (admin global)",
 *   @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
 *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, default=20)),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/PaginatedTenants")),
 *   @OA\Response(response=401, description="UNAUTHENTICATED", @OA\JsonContent(ref="#/components/schemas/Error")),
 *   @OA\Response(response=403, description="ACCESS_DENIED", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Post(
 *   path="/tenants",
 *   tags={"Tenants"},
 *   security={{"sanctum": {}}},
 *   summary="Criar tenant (admin global)",
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TenantStore")),
 *   @OA\Response(response=201, description="Criado", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Tenant"))),
 *   @OA\Response(response=401, description="UNAUTHENTICATED", @OA\JsonContent(ref="#/components/schemas/Error")),
 *   @OA\Response(response=403, description="ACCESS_DENIED", @OA\JsonContent(ref="#/components/schemas/Error")),
 *   @OA\Response(response=422, description="VALIDATION_ERROR", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Get(
 *   path="/tenants/{id}",
 *   tags={"Tenants"},
 *   security={{"sanctum": {}}},
 *   summary="Detalhar tenant",
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Tenant"))),
 *   @OA\Response(response=404, description="NOT_FOUND", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Put(
 *   path="/tenants/{id}",
 *   tags={"Tenants"},
 *   security={{"sanctum": {}}},
 *   summary="Atualizar tenant",
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TenantUpdate")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Tenant"))),
 *   @OA\Response(response=422, description="VALIDATION_ERROR", @OA\JsonContent(ref="#/components/schemas/Error")),
 *   @OA\Response(response=404, description="NOT_FOUND", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Delete(
 *   path="/tenants/{id}",
 *   tags={"Tenants"},
 *   security={{"sanctum": {}}},
 *   summary="Excluir tenant (409 se houver vínculos)",
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *   @OA\Response(response=204, description="Sem conteúdo"),
 *   @OA\Response(response=409, description="TENANT_NOT_EMPTY", @OA\JsonContent(ref="#/components/schemas/Error")),
 *   @OA\Response(response=404, description="NOT_FOUND", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 */
final class Tenants {}
