<?php

namespace App\Repositories\Contracts;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\CreateUserTokenDTO;
use App\DTOs\User\UserFilterDTO;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function create(CreateUserDTO $dto): User;

    public function createToken(CreateUserTokenDTO $dto): UserToken;

    public function findByEmail(string $email): ?User;

    public function deleteToken(string $token): bool;

    public function findById(int $id): ?User;

    public function update(User $user, array $data): bool;

    public function delete(User $user, bool $real = false): bool;

    public function getFiltered(UserFilterDTO $dto): LengthAwarePaginator;
}
