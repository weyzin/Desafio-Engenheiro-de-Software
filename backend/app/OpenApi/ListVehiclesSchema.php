<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ListVehicles",
    type: "object",
    properties: [
        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Vehicle")),
        new OA\Property(property: "meta", ref: "#/components/schemas/ListMeta"),
        new OA\Property(property: "links", ref: "#/components/schemas/ListLinks"),
    ]
)]
final class ListVehiclesSchema {}
