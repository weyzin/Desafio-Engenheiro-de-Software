<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ItemUser",
    type: "object",
    properties: [
        new OA\Property(property: "data", ref: "#/components/schemas/User"),
    ]
)]
final class ItemUserSchema {}
