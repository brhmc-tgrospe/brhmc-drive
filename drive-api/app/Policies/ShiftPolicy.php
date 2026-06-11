<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shift;
use Illuminate\Auth\Access\Response;

class ShiftPolicy
{
    public function before(User $user, string $ability): ?Response
    {
        if (!$user->isActive()) return Response::deny('Account inactive.');
        return null;
    }

    public function viewAny(User $user): bool
    {
        // Dispatchers can view all. Drivers can hit the endpoint, but the controller will filter it to their own shifts.
        return true; 
    }

    public function view(User $user, Shift $shift): Response
    {
        // Users with manage_schedules can view any shift. Drivers can only view their assigned shift.
        if ($user->hasPermission('manage_schedules') || $user->id === $shift->driver_id) {
            return Response::allow();
        }

        return Response::deny('You are not assigned to this shift.');
    }

    public function create(User $user): Response
    {
        return $user->hasPermission('manage_schedules')
            ? Response::allow()
            : Response::deny('You do not have permission to schedule shifts.');
    }

    public function update(User $user, Shift $shift): Response
    {
        return $user->hasPermission('manage_schedules')
            ? Response::allow()
            : Response::deny('You do not have permission to modify schedules.');
    }

    public function delete(User $user, Shift $shift): Response
    {
        return $user->hasPermission('manage_schedules')
            ? Response::allow()
            : Response::deny('You do not have permission to cancel shifts.');
    }
}