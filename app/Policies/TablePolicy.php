<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;

class TablePolicy
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

    public function view(User $user, Table $table): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->administeredRestaurants()->where('id', $table->restaurant->id)->exists()) {
            return true;
        }

        return false;
    }
}
