<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ListTenants",
    type: "object",
    properties: [
        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Tenant")),
        new OA\Property(property: "meta", ref: "#/components/schemas/ListMeta"),
        new OA\Property(property: "links", ref: "#/components/schemas/ListLinks"),
    ]
)]
final class ListTenantsSchema {}
