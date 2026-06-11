<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class VehicleRestored extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $vehicleUnit;
    public $mechanicName;

    public function __construct($vehicleUnit, $mechanicName)
    {
        $this->vehicleUnit = $vehicleUnit;
        $this->mechanicName = $mechanicName;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'vehicle_unit' => $this->vehicleUnit,
            'mechanic_name' => $this->mechanicName,
            'message' => "{$this->vehicleUnit} has been repaired by {$this->mechanicName} and is now READY.",
            'type' => 'restored'
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}