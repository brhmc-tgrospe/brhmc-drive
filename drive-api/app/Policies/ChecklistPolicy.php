<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Checklist;
use Illuminate\Auth\Access\Response;

class ChecklistPolicy
{
    public function before(User $user, string $ability): ?Response
    {
        if (!$user->isActive()) return Response::deny('Account inactive.');
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Checklist $checklist): bool
    {
        return true;
    }

    public function create(User $user): Response
    {
        // Both drivers and dispatchers need to create checklists
        return Response::allow();
    }

    /**
     * For the DispatcherSignatureModal.vue (Approve/Reject)
     */
    public function review(User $user, Checklist $checklist): Response
    {
        return $user->hasPermission('manage_fleet') || $user->hasPermission('manage_schedules')
            ? Response::allow()
            : Response::deny('You do not have authorization to approve or reject checklists.');
    }
}