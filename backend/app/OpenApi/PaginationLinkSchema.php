<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "PaginationLink",
    type: "object",
    properties: [
        new OA\Property(property: "url", type: "string", nullable: true, example: "http://localhost:8080/api/v1/tenants?page=2"),
        new OA\Property(property: "label", type: "string", example: "2"),
        new OA\Property(property: "active", type: "boolean", example: false),
    ]
)]
final class PaginationLinkSchema {}
