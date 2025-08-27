<?php

namespace App\OpenApi\Paths;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *   path="/users",
 *   tags={"Users"},
 *   security={{"sanctum": {}}},
 *   summary="Listar usuários (admin global)",
 *   @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
 *   @OA\Parameter(name="tenant_id", in="query", @OA\Schema(type="string", format="uuid")),
 *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/PaginatedUsers")),
 *   @OA\Response(response=401, description="UNAUTHENTICATED", @OA\JsonContent(ref="#/components/schemas/Error")),
 *   @OA\Response(response=403, description="ACCESS_DENIED", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Post(
 *   path="/users",
 *   tags={"Users"},
 *   security={{"sanctum": {}}},
 *   summary="Criar usuário",
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserStore")),
 *   @OA\Response(response=201, description="Criado", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/User"))),
 *   @OA\Response(response=422, description="VALIDATION_ERROR", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Get(
 *   path="/users/{id}",
 *   tags={"Users"},
 *   security={{"sanctum": {}}},
 *   summary="Ver usuário",
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/User"))),
 *   @OA\Response(response=404, description="NOT_FOUND", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Put(
 *   path="/users/{id}",
 *   tags={"Users"},
 *   security={{"sanctum": {}}},
 *   summary="Atualizar usuário",
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserUpdate")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/User"))),
 *   @OA\Response(response=422, description="VALIDATION_ERROR", @OA\JsonContent(ref="#/components/schemas/Error")),
 *   @OA\Response(response=404, description="NOT_FOUND", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Delete(
 *   path="/users/{id}",
 *   tags={"Users"},
 *   security={{"sanctum": {}}},
 *   summary="Excluir usuário",
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=204, description="Sem conteúdo"),
 *   @OA\Response(response=404, description="NOT_FOUND", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 */
final class Users {}
