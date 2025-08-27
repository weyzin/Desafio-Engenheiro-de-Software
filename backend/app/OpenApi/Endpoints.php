<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class Endpoints
{
    // --- Auth ---

    #[OA\Post(
        path: "/auth/login",
        tags: ["Auth"],
        summary: "Login",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/LoginInput")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "OK",
                content: new OA\JsonContent(ref: "#/components/schemas/LoginResponse")
            ),
            new OA\Response(response: 401, description: "Credenciais inválidas"),
        ]
    )]
    public function login() {}

    // --- Profile ---

    #[OA\Get(
        path: "/me",
        tags: ["Profile"],
        summary: "Dados do usuário autenticado",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "OK"),
            new OA\Response(response: 401, description: "Não autenticado"),
        ]
    )]
    public function me() {}

    // --- Tenants (lista) ---

    #[OA\Get(
        path: "/tenants",
        tags: ["Tenants"],
        summary: "Listar tenants",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "q", in: "query", description: "Busca por nome/slug", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "OK",
                content: new OA\JsonContent(ref: "#/components/schemas/ListTenants")
            ),
            new OA\Response(response: 401, description: "Não autenticado"),
            new OA\Response(response: 403, description: "Sem permissão"),
        ]
    )]
    public function listTenants() {}

    // --- Vehicles (lista) ---

    #[OA\Get(
        path: "/vehicles",
        tags: ["Vehicles"],
        summary: "Listar veículos",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "brand", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "model", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "year", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["available","reserved","sold"])),
            new OA\Parameter(name: "price_min", in: "query", schema: new OA\Schema(type: "number", format: "float")),
            new OA\Parameter(name: "price_max", in: "query", schema: new OA\Schema(type: "number", format: "float")),
            new OA\Parameter(name: "sort", in: "query", description: "price_asc|price_desc|year_asc|year_desc|created_asc|created_desc", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "OK",
                content: new OA\JsonContent(ref: "#/components/schemas/ListVehicles")
            ),
            new OA\Response(response: 401, description: "Não autenticado"),
            new OA\Response(response: 403, description: "Sem permissão"),
        ]
    )]
    public function listVehicles() {}
}
