<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function updateMe(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    public function deleteMe(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }
}
