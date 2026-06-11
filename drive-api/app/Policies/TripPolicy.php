<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Trip;
use Illuminate\Auth\Access\Response;

class TripPolicy
{
    public function before(User $user, string $ability): ?Response
    {
        if (!$user->isActive()) return Response::deny('Account inactive.');
        return null;
    }

    /**
     * Only dispatchers can explicitly clear a trip for departure (Phase 1)
     */
    public function clearForDeparture(User $user, Trip $trip): Response
    {
        return $user->hasPermission('execute_shifts') || $user->hasPermission('manage_schedules')
            ? Response::allow()
            : Response::deny('Only authorized dispatchers can clear a vehicle for departure.');
    }

    /**
     * Only the assigned driver can advance the timeline phases (Phase 0, 2, 3, 4)
     */
    public function advancePhase(User $user, Trip $trip): Response
    {
        // We check the associated shift to see if this driver owns this trip
        if ($user->id === $trip->shift->driver_id) {
            return Response::allow();
        }

        // Admins/Dispatchers can force-advance for testing/emergencies if needed
        if ($user->hasPermission('manage_schedules')) {
            return Response::allow();
        }

        return Response::deny('You are not the assigned driver for this trip.');
    }
}