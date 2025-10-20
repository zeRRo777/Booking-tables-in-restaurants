<?php

namespace App\Repositories\Eloquent;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\CreateUserTokenDTO;
use App\DTOs\User\UserFilterDTO;
use App\Models\User;
use App\Models\UserToken;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function getFiltered(UserFilterDTO $dto): LengthAwarePaginator
    {
        $query = User::query();

        $query->when($dto->name, function ($q) use ($dto) {
            $q->where('name', 'like', "%{$dto->name}%");
        });

        $query->when($dto->email, function ($q) use ($dto) {
            $q->where('email', 'like', "%{$dto->email}%");
        });

        $query->when($dto->phone, function ($q) use ($dto) {
            $q->where('phone', 'like', "%{$dto->phone}%");
        });

        $query->when(!is_null($dto->is_blocked), function ($q) use ($dto) {
            $q->where('is_blocked', $dto->is_blocked);
        });

        return $query->paginate($dto->per_page);
    }
}
