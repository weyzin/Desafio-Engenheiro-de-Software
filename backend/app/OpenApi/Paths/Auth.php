<?php

namespace App\OpenApi\Paths;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *   path="/auth/login",
 *   tags={"Auth"},
 *   summary="Login",
 *   description="Superuser deve logar **sem** `X-Tenant`. Owner/agent **devem** enviar `X-Tenant` coerente.",
 *   @OA\Parameter(ref="#/components/parameters/XTenantHeader"),
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       required={"email","password"},
 *       @OA\Property(property="email", type="string", format="email"),
 *       @OA\Property(property="password", type="string")
 *     )
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="OK",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="data", ref="#/components/schemas/AuthTokenResponse")
 *     )
 *   ),
 *   @OA\Response(response=400, description="SUPERUSER_TENANT_NOT_ALLOWED", @OA\JsonContent(ref="#/components/schemas/Error")),
 *   @OA\Response(response=401, description="INVALID_CREDENTIALS", @OA\JsonContent(ref="#/components/schemas/Error")),
 *   @OA\Response(response=422, description="VALIDATION_ERROR", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 *
 * @OA\Post(
 *   path="/auth/forgot",
 *   tags={"Auth"},
 *   summary="Solicitar reset de senha (sempre 200)",
 *   @OA\RequestBody(required=false, @OA\JsonContent(
 *     @OA\Property(property="email", type="string", format="email")
 *   )),
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *   path="/auth/logout",
 *   tags={"Auth"},
 *   security={{"sanctum": {}}},
 *   summary="Logout",
 *   @OA\Response(response=204, description="Sem conteúdo"),
 *   @OA\Response(response=401, description="UNAUTHENTICATED", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 */
final class Auth {}
