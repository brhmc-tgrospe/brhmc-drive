<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\Response;

class VehiclePolicy
{
    /**
     * Universal check: User must be active to do ANYTHING.
     */
    public function before(User $user, string $ability): ?Response
    {
        if (!$user->isActive()) {
            return Response::deny('Your account is currently inactive or suspended.');
        }
        return null; // Continue to specific policy methods
    }

    public function viewAny(User $user): bool
    {
        return true; // All active users (Drivers & Dispatchers) can view the fleet list
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return true; 
    }

    public function create(User $user): Response
    {
        return $user->hasPermission('manage_fleet') 
            ? Response::allow() 
            : Response::deny('You do not have permission to add new vehicles.');
    }

    public function update(User $user, Vehicle $vehicle): Response
    {
        return $user->hasPermission('manage_fleet')
            ? Response::allow()
            : Response::deny('You do not have permission to modify vehicle details.');
    }

    public function delete(User $user, Vehicle $vehicle): Response
    {
        return $user->hasPermission('manage_fleet')
            ? Response::allow()
            : Response::deny('You do not have permission to delete vehicles.');
    }
}