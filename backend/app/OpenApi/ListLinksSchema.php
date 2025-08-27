<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ListLinks",
    type: "object",
    properties: [
        new OA\Property(property: "next", type: "string", nullable: true, example: "http://localhost:8080/api/v1/tenants?page=2"),
        new OA\Property(property: "prev", type: "string", nullable: true, example: null),
    ]
)]
final class ListLinksSchema {}
