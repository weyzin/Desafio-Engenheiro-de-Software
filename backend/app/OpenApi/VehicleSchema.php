<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Vehicle",
    description: "Veículo",
    required: ["id","brand","model","year","price","status"],
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 123),
        new OA\Property(property: "brand", type: "string", example: "Toyota"),
        new OA\Property(property: "model", type: "string", example: "Corolla"),
        new OA\Property(property: "version", type: "string", nullable: true, example: "Altis 2.0 Flex CVT"),
        new OA\Property(property: "year", type: "integer", example: 2023),
        new OA\Property(property: "km", type: "integer", nullable: true, example: 35000),
        new OA\Property(property: "price", type: "number", format: "float", example: 125000.00),
        new OA\Property(property: "status", type: "string", enum: ["available","reserved","sold"], example: "available"),
        new OA\Property(property: "notes", type: "string", nullable: true, example: "Cliente João interessado; retorno semana que vem."),
        new OA\Property(
            property: "images",
            type: "array",
            items: new OA\Items(type: "string", format: "uri"),
            example: ["https://cdn.exemplo.com/veh/1.jpg"]
        ),
        new OA\Property(property: "created_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", nullable: true),
    ]
)]
final class VehicleSchema {}
