<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentReported implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $incidentId;
    public $issueType;

    public function __construct($incidentId, $issueType)
    {
        $this->incidentId = $incidentId;
        $this->issueType = $issueType;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('fleet-updates'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'incident_id' => $this->incidentId,
            'issue_type' => $this->issueType,
            'message' => 'Emergency Alert: ' . $this->issueType
        ];
    }
}