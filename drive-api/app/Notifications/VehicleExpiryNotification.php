<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class VehicleExpiryNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $vehicleUnit;
    public $type; // 'registration' or 'insurance'
    public $status; // 'warning' (upcoming) or 'urgent' (expired)
    public $messageText;

    public function __construct($vehicleUnit, $type, $status, $messageText)
    {
        $this->vehicleUnit = $vehicleUnit;
        $this->type = $type;
        $this->status = $status;
        $this->messageText = $messageText;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'vehicle_unit' => $this->vehicleUnit,
            'expiry_type' => $this->type,
            'message' => $this->messageText,
            'type' => $this->status
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
