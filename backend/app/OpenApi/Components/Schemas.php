<?php

namespace App\OpenApi\Components;

use OpenApi\Annotations as OA;

/**
 * ÚNICO bloco de Components do documento.
 * Aqui ficam: SecuritySchemes, Parameters e todos os Schemas reutilizáveis.
 *
 * @OA\Components(
 *   @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Use no header Authorization: Bearer <token>"
 *   ),
 *
 *   @OA\Parameter(
 *     parameter="XTenantHeader",
 *     name="X-Tenant",
 *     in="header",
 *     required=false,
 *     description="Slug do tenant. Obrigatório p/ owner/agent; proibido p/ superuser no /auth/login; exigido em /vehicles.",
 *     @OA\Schema(type="string", example="acme")
 *   ),
 *
 *   @OA\Schema(
 *     schema="Error",
 *     required={"code","message"},
 *     @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
 *     @OA\Property(property="message", type="string", example="Erro de validação."),
 *     @OA\Property(
 *       property="errors",
 *       type="object",
 *       nullable=true,
 *       description="Mapa de campo => lista de mensagens.",
 *       @OA\AdditionalProperties(
 *         type="array",
 *         @OA\Items(type="string", example="O campo email é obrigatório.")
 *       )
 *     )
 *   ),
 *
 *   @OA\Schema(
 *     schema="PaginationMeta",
 *     @OA\Property(property="total", type="integer", example=123),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="per_page", type="integer", example=20),
 *     @OA\Property(property="last_page", type="integer", example=7),
 *     @OA\Property(property="from", type="integer", nullable=true, example=1),
 *     @OA\Property(property="to", type="integer", nullable=true, example=20),
 *     @OA\Property(property="hint", type="string", nullable=true, example="Superuser sem tenant ativo. Envie X-Tenant para listar veículos.")
 *   ),
 *
 *   @OA\Schema(
 *     schema="PaginationLinks",
 *     @OA\Property(property="next", type="string", nullable=true, example="http://localhost:8080/api/v1/tenants?page=2"),
 *     @OA\Property(property="prev", type="string", nullable=true, example=null)
 *   ),
 *
 *   @OA\Schema(
 *     schema="AuthTokenResponse",
 *     @OA\Property(property="token", type="string", example="1|eyJ0eXAiOiJKV1QiLCJh..."),
 *     @OA\Property(property="token_type", type="string", example="Bearer"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 *   ),
 *
 *   @OA\Schema(
 *     schema="User",
 *     required={"id","email","name","role"},
 *     @OA\Property(property="id", type="integer", example=2),
 *     @OA\Property(property="tenant_id", type="string", format="uuid", nullable=true, example="11111111-1111-1111-1111-111111111111"),
 *     @OA\Property(property="name", type="string", maxLength=120, example="Jane Doe"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=190, example="owner@acme.com"),
 *     @OA\Property(property="role", type="string", enum={"superuser","owner","agent"}, example="owner"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 *   ),
 *
 *   @OA\Schema(
 *     schema="UserStore",
 *     required={"name","email","password","role"},
 *     @OA\Property(property="tenant_id", type="string", format="uuid", nullable=true, description="Obrigatório exceto quando role=superuser"),
 *     @OA\Property(property="name", type="string", maxLength=120),
 *     @OA\Property(property="email", type="string", format="email", maxLength=190),
 *     @OA\Property(property="password", type="string", minLength=8),
 *     @OA\Property(property="role", type="string", enum={"superuser","owner","agent"})
 *   ),
 *
 *   @OA\Schema(
 *     schema="UserUpdate",
 *     required={"name","email","role"},
 *     @OA\Property(property="tenant_id", type="string", format="uuid", nullable=true, description="Obrigatório exceto quando role=superuser"),
 *     @OA\Property(property="name", type="string", maxLength=120),
 *     @OA\Property(property="email", type="string", format="email", maxLength=190),
 *     @OA\Property(property="password", type="string", nullable=true, minLength=8),
 *     @OA\Property(property="role", type="string", enum={"superuser","owner","agent"})
 *   ),
 *
 *   @OA\Schema(
 *     schema="PaginatedUsers",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 *   ),
 *
 *   @OA\Schema(
 *     schema="Tenant",
 *     required={"id","name","slug"},
 *     @OA\Property(property="id", type="string", format="uuid", example="11111111-1111-1111-1111-111111111111"),
 *     @OA\Property(property="name", type="string", minLength=2, maxLength=120, example="ACME Inc."),
 *     @OA\Property(property="slug", type="string", minLength=2, maxLength=60, example="acme", description="kebab-case; minúsculas/números/hífens"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 *   ),
 *
 *   @OA\Schema(
 *     schema="TenantStore",
 *     required={"name","slug"},
 *     @OA\Property(property="name", type="string", maxLength=120),
 *     @OA\Property(property="slug", type="string", maxLength=60, example="acme")
 *   ),
 *
 *   @OA\Schema(
 *     schema="TenantUpdate",
 *     required={"name","slug"},
 *     @OA\Property(property="name", type="string", minLength=2, maxLength=120),
 *     @OA\Property(property="slug", type="string", minLength=2, maxLength=60, example="acme")
 *   ),
 *
 *   @OA\Schema(
 *     schema="PaginatedTenants",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Tenant")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 *   ),
 *
 *   @OA\Schema(
 *     schema="VehicleImage",
 *     @OA\Property(property="url", type="string", format="uri", example="https://cdn.example.com/veh/1.jpg"),
 *     @OA\Property(property="caption", type="string", nullable=true, example="Frontal")
 *   ),
 *
 *   @OA\Schema(
 *     schema="Vehicle",
 *     required={"id","tenant_id","brand","model","price","year","status"},
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="tenant_id", type="string", format="uuid", example="11111111-1111-1111-1111-111111111111"),
 *     @OA\Property(property="brand", type="string", maxLength=80, example="Toyota"),
 *     @OA\Property(property="model", type="string", maxLength=80, example="Corolla"),
 *     @OA\Property(property="version", type="string", nullable=true, maxLength=120, example="XEi 2.0"),
 *     @OA\Property(property="year", type="integer", minimum=1900, maximum=2100, example=2022),
 *     @OA\Property(property="km", type="integer", nullable=true, minimum=0, example=35000),
 *     @OA\Property(property="price", type="number", format="float", minimum=0, example=105000.00),
 *     @OA\Property(property="status", type="string", enum={"available","reserved","sold"}, example="available"),
 *     @OA\Property(property="notes", type="string", nullable=true, maxLength=1000),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string", format="uri")),
 *     @OA\Property(property="created_by", type="integer", nullable=true),
 *     @OA\Property(property="updated_by", type="integer", nullable=true),
 *     @OA\Property(property="deleted_by", type="integer", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 *   ),
 *
 *   @OA\Schema(
 *     schema="VehicleStore",
 *     required={"brand","model","year","price"},
 *     @OA\Property(property="brand", type="string", maxLength=80),
 *     @OA\Property(property="model", type="string", maxLength=80),
 *     @OA\Property(property="version", type="string", nullable=true, maxLength=120),
 *     @OA\Property(property="year", type="integer", minimum=1900, maximum=2100),
 *     @OA\Property(property="km", type="integer", nullable=true, minimum=0),
 *     @OA\Property(property="price", type="number", format="float", minimum=0),
 *     @OA\Property(property="status", type="string", nullable=true, enum={"available","reserved","sold"}),
 *     @OA\Property(property="notes", type="string", nullable=true, maxLength=1000),
 *     @OA\Property(property="images", type="array", maxItems=10, @OA\Items(type="string", format="uri"))
 *   ),
 *
 *   @OA\Schema(
 *     schema="VehicleUpdate",
 *     @OA\Property(property="brand", type="string", maxLength=80, nullable=true),
 *     @OA\Property(property="model", type="string", maxLength=80, nullable=true),
 *     @OA\Property(property="version", type="string", nullable=true, maxLength=120),
 *     @OA\Property(property="year", type="integer", minimum=1900, maximum=2100, nullable=true),
 *     @OA\Property(property="km", type="integer", minimum=0, nullable=true),
 *     @OA\Property(property="price", type="number", format="float", minimum=0, nullable=true),
 *     @OA\Property(property="status", type="string", enum={"available","reserved","sold"}, nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true, maxLength=1000),
 *     @OA\Property(property="images", type="array", maxItems=10, @OA\Items(type="string", format="uri"))
 *   ),
 *
 *   @OA\Schema(
 *     schema="PaginatedVehicles",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Vehicle")),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 *   )
 * )
 */
final class Schemas {}
