<?php

namespace App\OpenApi\Paths;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *   path="/me",
 *   tags={"Profile"},
 *   security={{"sanctum": {}}},
 *   summary="Dados do usuário autenticado",
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(
 *     @OA\Property(property="data",
 *       type="object",
 *       @OA\Property(property="id", type="integer"),
 *       @OA\Property(property="email", type="string", format="email"),
 *       @OA\Property(property="name", type="string"),
 *       @OA\Property(property="role", type="string", enum={"superuser","owner","agent"}),
 *       @OA\Property(property="tenant_id", type="string", format="uuid", nullable=true),
 *       @OA\Property(property="active_tenant", type="string", format="uuid", nullable=true)
 *     )
 *   )),
 *   @OA\Response(response=401, description="UNAUTHENTICATED", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 */
final class Profile {}
