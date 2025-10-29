<?php

namespace App\Policies;

use App\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return !$user->isBlocked();
    }
}
