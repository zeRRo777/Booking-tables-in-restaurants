<?php

namespace App\Policies;

use App\Models\User;

class ChainPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('superadmin');
    }
}
