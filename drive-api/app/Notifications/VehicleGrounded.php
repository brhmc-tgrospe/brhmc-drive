<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class VehicleGrounded extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $vehicleUnit;
    public $reason;

    public function __construct($vehicleUnit, $reason)
    {
        $this->vehicleUnit = $vehicleUnit;
        $this->reason = $reason;
    }

    // Send to both Database (for the Bell history) and Broadcast (for the live red dot)
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'vehicle_unit' => $this->vehicleUnit,
            'reason' => $this->reason,
            'message' => "{$this->vehicleUnit} has been grounded. Reason: {$this->reason}",
            'type' => 'grounded'
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}