<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ListMeta",
    type: "object",
    properties: [
        new OA\Property(property: "total", type: "integer", example: 42),
        new OA\Property(property: "page", type: "integer", example: 1),
        new OA\Property(property: "per_page", type: "integer", example: 20),
        new OA\Property(property: "last_page", type: "integer", example: 3),
        new OA\Property(property: "from", type: "integer", nullable: true, example: 1),
        new OA\Property(property: "to", type: "integer", nullable: true, example: 20),
        new OA\Property(
            property: "links",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/PaginationLink"),
        ),
    ]
)]
final class ListMetaSchema {}
