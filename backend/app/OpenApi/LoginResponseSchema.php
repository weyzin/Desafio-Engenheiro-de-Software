<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "LoginResponse",
    type: "object",
    properties: [
        new OA\Property(
            property: "data",
            type: "object",
            properties: [
                new OA\Property(property: "token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOi..."),
            ]
        ),
    ]
)]
final class LoginResponseSchema {}
