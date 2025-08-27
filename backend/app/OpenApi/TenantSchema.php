<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Tenant",
    description: "Tenant",
    required: ["id","name","slug"],
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "4aa8412a-ce1f-4179-9b82-09d8c5a7cd23"),
        new OA\Property(property: "name", type: "string", maxLength: 120, example: "Nova LTDA"),
        new OA\Property(property: "slug", type: "string", maxLength: 60, example: "nova"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", nullable: true),
    ]
)]
final class TenantSchema {}
