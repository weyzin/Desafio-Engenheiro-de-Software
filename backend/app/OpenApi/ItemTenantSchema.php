<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ItemTenant",
    type: "object",
    properties: [
        new OA\Property(property: "data", ref: "#/components/schemas/Tenant"),
    ]
)]
final class ItemTenantSchema {}
