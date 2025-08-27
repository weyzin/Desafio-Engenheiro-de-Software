<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "LoginInput",
    type: "object",
    required: ["email","password"],
    properties: [
        new OA\Property(property: "email", type: "string", format: "email", example: "admin@root.com"),
        new OA\Property(property: "password", type: "string", format: "password", example: "Password!123"),
    ]
)]
final class LoginInputSchema {}
