<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;

class RestaurantPolicy
{
    public function view(?User $user, Restaurant $restaurant): bool
    {
        if ($restaurant->status->name === 'active') {
            return true;
        }

        if ($user) {
            if ($user->hasRole('superadmin')) {
                return true;
            }

            if ($user->hasRole('admin_chain')) {
                return $restaurant->chain()->superAdmins()->where('user_id', $user->id)->exists();
            }

            if ($user->hasRole('admin_restaurant')) {
                return $restaurant->administrators()->where('user_id', $user->id)->exists();
            }
        }

        return false;
    }
}
