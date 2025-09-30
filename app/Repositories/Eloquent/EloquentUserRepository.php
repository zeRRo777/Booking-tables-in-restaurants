<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CreateUserDTO;
use App\DTOs\CreateUserTokenDTO;
use App\Models\User;
use App\Models\UserToken;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(CreateUserDTO $dto): User
    {
        return User::create([
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'name' => $dto->name,
            'phone' => $dto->phone,
        ]);
    }

    public function createToken(CreateUserTokenDTO $dto): UserToken
    {
        return UserToken::create([
            'user_id' => $dto->user_id,
            'token' => $dto->token,
            'expires_at' => $dto->expires_at,
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function deleteToken(string $token): bool
    {
        return UserToken::where('token', $token)->delete();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user, bool $real = false): bool
    {
        return $real ? $user->forceDelete() : $user->delete();
    }
}
