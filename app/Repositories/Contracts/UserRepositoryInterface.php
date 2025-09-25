<?php

namespace App\Repositories\Contracts;

use App\DTOs\CreateUserDTO;
use App\DTOs\CreateUserTokenDTO;
use App\Models\User;
use App\Models\UserToken;

interface UserRepositoryInterface
{
    public function create(CreateUserDTO $dto): User;

    public function createToken(CreateUserTokenDTO $dto): UserToken;

    public function findByEmail(string $email): ?User;

    public function deleteToken(string $token): bool;

    public function findById(int $id): ?User;

    public function update(User $user, array $data): bool;
}
