<?php

namespace App\Services;

use App\DTOs\Contracts\UpdateUserDtoInterface;
use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use App\DTOs\UserFilterDTO;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\UserUpdateQueryException;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
    ) {}

    public function createUser(CreateUserDTO $dto): User
    {
        $user = DB::transaction(function () use ($dto): User {
            $user = $this->userRepository->create($dto);
            $user->roles()->attach(Role::where('name', 'user')->first());
            Log::info('Новый пользователь создан успешно', ['user_id' => $user->id]);
            return $user;
        });

        return $user;
    }

    public function updateUser(int $userId, UpdateUserDtoInterface $dto): User
    {
        $user = $this->getUser($userId);

        $data = array_filter(
            $dto->toArray(),
            fn($value) => !is_null($value)
        );

        if (empty($data)) {
            return $user;
        }

        if (isset($data['email'])) {
            $data['email_verified_at'] = null;
        }

        if (isset($data['phone'])) {
            $data['phone_verified_at'] = null;
        }

        $this->userRepository->update($user, $data);

        return $user->refresh();
    }

    public function deleteUser(User $user, bool $real = false): void
    {
        DB::transaction(function () use ($user, $real): bool {
            return $this->userRepository->delete($user, $real);
        });
    }

    public function getUsers(UserFilterDTO $dto): LengthAwarePaginator
    {
        return $this->userRepository->getFiltered($dto);
    }

    public function getUser(int $id): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new UserNotFoundException('Пользователь не найден!');
        }

        return $user;
    }
}
