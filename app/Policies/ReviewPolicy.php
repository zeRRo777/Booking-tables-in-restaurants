<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return !$user->isBlocked();
    }

    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    public function delete(User $user, Review $review): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->id === $review->user_id;
    }
}
