<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'start_time' => $this->scheduled_start ?? clone $this->start_time, // Fallback for model property name differences
            'end_time' => $this->scheduled_end ?? clone $this->end_time,
            'shift_duration' => isset($this->shift_duration) ? $this->shift_duration : Carbon::parse($this->scheduled_start ?? $this->start_time)->diffInHours($this->scheduled_end ?? $this->end_time),
            
            'driver_id' => $this->driver_id,
            'driver' => clone $this->whenLoaded('driver', function () {
                return [
                    'id' => $this->driver->id,
                    'first_name' => $this->driver->first_name,
                    'last_name' => $this->driver->last_name,
                ];
            }),
            
            'vehicle_id' => $this->vehicle_id,
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            
            'trip' => clone $this->whenLoaded('trip'),
        ];
    }
}