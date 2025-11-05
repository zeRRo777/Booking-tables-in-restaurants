<?php

namespace App\Policies;

use App\Models\User;

class ReservationPolicy
{
    public function create(User $user)
    {
        return !$user->isBlocked();
    }
}
