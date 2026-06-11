<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Use ShouldBroadcastNow for immediate map updates
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vehicle_id;
    public $unit_id;
    public $latitude;
    public $longitude;
    public $speed;
    public $status;

    public function __construct($vehicle, $latitude, $longitude, $speed)
    {
        $this->vehicle_id = $vehicle->id;
        $this->unit_id = $vehicle->unit_id;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->speed = $speed;
        $this->status = $vehicle->status;
    }

    /**
     * Broadcast on a secured, private dispatcher channel.
     */
    public function broadcastOn()
    {
        return new PrivateChannel('dispatch.fleet');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'vehicle.moved';
    }
}