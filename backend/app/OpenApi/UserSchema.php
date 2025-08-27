<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    description: "Usuário",
    required: ["id","email","role"],
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "tenant_id", type: "string", format: "uuid", nullable: true, example: "4aa8412a-ce1f-4179-9b82-09d8c5a7cd23"),
        new OA\Property(property: "name", type: "string", nullable: true, example: "Alice Souza"),
        new OA\Property(property: "email", type: "string", format: "email", example: "alice@acme.com"),
        new OA\Property(property: "role", type: "string", enum: ["agent","owner","superuser"], example: "owner"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", nullable: true),
    ]
)]
final class UserSchema {}
