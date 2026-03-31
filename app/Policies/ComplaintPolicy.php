<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Complaint $complaint): bool
    {
        return $user->isStaff() || $complaint->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Complaint $complaint): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $complaint->canBeEditedBy($user);
    }

    public function delete(User $user, Complaint $complaint): bool
    {
        return $complaint->canBeWithdrawnBy($user);
    }
}
