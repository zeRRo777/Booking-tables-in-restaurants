<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface PhoneChangeRepositoryInterface
{
    public function createOrUpdate(User $user, string $code, string $phone): bool;

    public function findByUser(User $user): ?object;

    public function deleteByUser(User $user): int;
}
