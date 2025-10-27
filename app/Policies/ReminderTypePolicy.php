<?php

namespace App\Policies;

use App\Models\User;

class ReminderTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function view(User $user): bool
    {
        return $user->hasRole('superadmin');
    }
}
