<?php

namespace App\Repositories\Contracts;

interface PasswordResetRepositoryInterface
{
    public function createOrUpdate(string $email, string $hashedToken): bool;

    public function findByEmail(string $email): ?object;

    public function deleteByEmail(string $email): bool;
}
