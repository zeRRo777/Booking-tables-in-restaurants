<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserTokensRepositoryInterface
{
    public function getUserTokens(User $user): array;

    public function deleteUserTokens(User $user): int;
}
