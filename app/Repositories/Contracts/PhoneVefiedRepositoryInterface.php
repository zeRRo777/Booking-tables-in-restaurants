<?php

namespace App\Repositories\Contracts;

interface PhoneVefiedRepositoryInterface
{
    public function createOrUpdate(string $phone, string $hashedCode): bool;

    public function findByPhone(string $phone): ?object;

    public function deleteByPhone(string $phone): int;
}
