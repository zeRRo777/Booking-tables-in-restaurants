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

    public function create(User $user): bool
    {
        if ($user->hasAnyRole(['superadmin', 'admin_chain'])) {
            return true;
        }

        return false;
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_chain')) {
            return $restaurant->restaurant_chain_id && $user->administeredChains()->where('id', $restaurant->restaurant_chain_id)->exists();
        }

        if ($user->hasRole('admin_restaurant')) {
            return $restaurant->administrators()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function delete(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_chain')) {
            return $restaurant->restaurant_chain_id && $user->administeredChains()->where('id', $restaurant->restaurant_chain_id)->exists();
        }

        return false;
    }

    public function changeStatus(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function viewAdmins(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $restaurant->chain()->superAdmins()->where('user_id', $user->id)->exists();
    }

    public function addAdmin(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $restaurant->chain()->superAdmins()->where('user_id', $user->id)->exists();
    }

    public function removeAdmin(User $user, Restaurant $restaurant, User $model): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($restaurant->chain()->superAdmins()->where('user_id', $user->id)->exists() && $restaurant->administrators()->where('user_id', $model->id)->exists()) {
            return true;
        }

        return false;
    }

    public function viewBlockedUsers(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_restaurant')) {
            return $restaurant->administrators()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('admin_chain')) {
            return $restaurant->chain()->superAdmins()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function addBlockedUser(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_restaurant')) {
            return $restaurant->administrators()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('admin_chain')) {
            return $restaurant->chain()->superAdmins()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function deleteBlockedUser(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_restaurant')) {
            return $restaurant->administrators()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('admin_chain')) {
            return $restaurant->chain()->superAdmins()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function viewStats(User $user, Restaurant $restaurant): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_restaurant')) {
            return $restaurant->administrators()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('admin_chain')) {
            return $restaurant->chain()->superAdmins()->where('user_id', $user->id)->exists();
        }

        return false;
    }
}
