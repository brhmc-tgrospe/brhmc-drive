<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'plate_number' => $this->plate_number,
            'make_model' => $this->make_model,
            'vehicle_type' => $this->vehicle_type,
            'odometer' => (int) $this->odometer,
            'base_location' => $this->base_location,
            'status' => $this->status,
            'image_path' => $this->image_path ? Storage::url($this->image_path) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}