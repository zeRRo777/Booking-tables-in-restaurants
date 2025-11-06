<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function create(User $user)
    {
        return !$user->isBlocked();
    }

    public function update(User $user, Reservation $reservation): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_restaurant')) {
            return $reservation->restaurant()->administeredRestaurants()->where('user_id', $user->id)->exists();
        }

        if ($reservation->user_id === $user->id && $reservation->status->name === 'Pending') {
            return true;
        }

        return false;
    }
}
