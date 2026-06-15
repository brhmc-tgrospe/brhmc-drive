<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Shift;

class ShiftScheduled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $shift;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Shift $shift)
    {
        $this->shift = $shift;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('driver.' . $this->shift->driver_id),
            new PrivateChannel('dispatch.alerts')
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'shift.scheduled';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $shiftData = $this->shift->load('vehicle', 'driver');
        return [
            'shift_id' => $shiftData->id,
            'driver_id' => $shiftData->driver_id,
            'scheduled_start' => $shiftData->scheduled_start,
            'scheduled_end' => $shiftData->scheduled_end,
            'vehicle_plate' => $shiftData->vehicle ? $shiftData->vehicle->plate_number : 'Unknown',
            'message' => 'New Shift Scheduled for ' . ($shiftData->driver ? $shiftData->driver->first_name : 'You')
        ];
    }
}
