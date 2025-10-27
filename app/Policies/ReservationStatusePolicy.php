<?php

namespace App\Policies;

use App\Models\User;

class ReservationStatusePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function view(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('superadmin');
    }
}
