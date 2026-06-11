<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergencyReported implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $emergency;

    public function __construct($emergencyData)
    {
        $this->emergency = $emergencyData;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('dispatch.alerts');
    }

    public function broadcastAs()
    {
        return 'emergency.triggered';
    }
}