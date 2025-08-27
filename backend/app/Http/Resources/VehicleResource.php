<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray($request)
    {
        // Mapeia coluna images_json ↔ campo "images" do contrato
        return [
            'id'         => $this->id,
            'tenant_id'  => (string) $this->tenant_id,
            'brand'      => $this->brand,
            'model'      => $this->model,
            'year'       => (int) $this->year,
            'price'      => (float) $this->price,
            'km'         => $this->km,
            'notes'      => $this->notes,
            'version'    => $this->version,
            'status'     => $this->status,
            'images'     => $this->images ?? [],
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'deleted_at' => optional($this->deleted_at)->toIso8601String(),
        ];
    }

    public function withResponse($request, $response)
    {
        // Preserva zeros fracionários em floats (ex.: 47000.0)
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRESERVE_ZERO_FRACTION);
    }
}
