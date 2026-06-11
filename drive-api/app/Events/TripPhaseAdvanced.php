<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripPhaseAdvanced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tripId;
    public $newPhase;
    public $driverName;
    public $driverId;

    public function __construct($tripId, $newPhase, $driverName = null, $driverId = null)
    {
        $this->tripId = $tripId;
        $this->newPhase = $newPhase;
        $this->driverName = $driverName;
        $this->driverId = $driverId;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('fleet-updates'),
        ];

        if ($this->driverId) {
            $channels[] = new PrivateChannel('driver.' . $this->driverId);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'trip_id' => $this->tripId,
            'phase' => $this->newPhase,
            'driver_name' => $this->driverName ?? 'A driver',
        ];
    }
}