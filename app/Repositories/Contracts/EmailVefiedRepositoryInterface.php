<?php

namespace App\Repositories\Contracts;

interface EmailVefiedRepositoryInterface
{
    public function createOrUpdate(string $email, string $hashedCode): bool;

    public function findByEmail(string $email): ?object;

    public function deleteByEmail(string $email): int;
}
