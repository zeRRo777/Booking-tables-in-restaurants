<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserTokensRepositoryInterface;

class EloquentUserTokensRepository implements UserTokensRepositoryInterface
{
    public function getUserTokens(User $user): array
    {
        return $user->tokens()->pluck('token')->toArray();
    }

    public function deleteUserTokens(User $user): int
    {
        return $user->tokens()->delete();
    }
}
