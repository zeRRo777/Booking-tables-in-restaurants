<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface EmailChangeRepositoryInterface
{
    public function createOrUpdate(User $user, string $token, string $email): bool;

    public function deleteByUser(User $user): bool;

    public function findByToken(string $token): object|null;
}
