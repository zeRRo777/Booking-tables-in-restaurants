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
}
