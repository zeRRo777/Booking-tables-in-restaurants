<?php

namespace App\Policies;

use App\Models\RestaurantChain;
use App\Models\User;

class ChainPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function update(User $user, RestaurantChain $chain): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_chain')) {
            return $chain->superAdmins()->where('user_id', $user->id)->exists();
        }

        return false;
    }
}
