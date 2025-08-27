<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ItemVehicle",
    type: "object",
    properties: [
        new OA\Property(property: "data", ref: "#/components/schemas/Vehicle"),
    ]
)]
final class ItemVehicleSchema {}
