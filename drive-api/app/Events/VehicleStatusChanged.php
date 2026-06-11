<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vehicle;
    public $oldStatus;
    public $newStatus;

    public function __construct($vehicle, $oldStatus, $newStatus)
    {
        $this->vehicle = $vehicle;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function broadcastOn()
    {
        return [
            new Channel('fleet-updates'),
        ];
    }

    public function broadcastAs()
    {
        return 'vehicle.status.changed';
    }

    /**
     * Control exactly what data is sent over the WebSocket.
     * We strip image_path because it can be a multi-MB Base64 string
     * that would exceed the WebSocket frame size limit and cause
     * Reverb to silently drop the message.
     */
    public function broadcastWith(): array
    {
        return [
            'vehicle' => [
                'id' => $this->vehicle->id ?? null,
                'unit_id' => $this->vehicle->unit_id ?? null,
                'plate_number' => $this->vehicle->plate_number ?? null,
                'make_model' => $this->vehicle->make_model ?? null,
                'vehicle_type' => $this->vehicle->vehicle_type ?? null,
            ],
            'oldStatus' => $this->oldStatus,
            'newStatus' => $this->newStatus,
        ];
    }
}
