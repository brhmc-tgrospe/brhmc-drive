<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChecklistSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $checklistId;
    public $driverName;
    public $vehicleUnit;
    public $type;
    public $scheduledStart;
    public $driverId;

    public function __construct($checklistId, $driverName = null, $vehicleUnit = null, $type = null, $scheduledStart = null, $driverId = null)
    {
        $this->checklistId = $checklistId;
        $this->driverName = $driverName;
        $this->vehicleUnit = $vehicleUnit;
        $this->type = $type;
        $this->scheduledStart = $scheduledStart;
        $this->driverId = $driverId;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('fleet-updates'),
        ];

        // Also send to the driver's personal channel
        if ($this->driverId) {
            $channels[] = new PrivateChannel('driver.' . $this->driverId);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        $typeLabel = $this->type === 'PRE_TRIP' ? 'Pre-Trip' : 'Post-Trip';

        return [
            'checklist_id' => $this->checklistId,
            'driver_name' => $this->driverName ?? 'Unknown Driver',
            'vehicle_unit' => $this->vehicleUnit ?? 'Unknown Vehicle',
            'type' => $typeLabel,
            'scheduled_start' => $this->scheduledStart,
        ];
    }
}