<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;

class RestaurantSchedulePolicy
{
    public function viewAny(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_restaurant')) {
            return $restaurant->administrators()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function view(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_restaurant')) {
            return $restaurant->administrators()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_restaurant')) {
            return $restaurant->administrators()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_restaurant')) {
            return $restaurant->administrators()->where('user_id', $user->id)->exists();
        }

        return false;
    }
}
