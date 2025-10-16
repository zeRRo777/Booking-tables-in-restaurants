<?php

namespace App\Services;

use App\DTOs\CreateUserDTO;
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
        protected AuthService $authService,
    ) {}

    public function createUser(CreateUserDTO $dto): array
    {
        $dataUser = DB::transaction(function () use ($dto): array {
            $user = $this->userRepository->create($dto);
            $user->roles()->attach(Role::where('name', 'user')->first());

            $token = $this->authService->createAndSaveToken($user);
            Log::info('Новый пользователь создан успешно', ['user_id' => $user->id]);
            return ['user' => $user, 'token' => $token->token];
        });

        return $dataUser;
    }

    public function updateUser(User $user, array $data): User
    {
        $result = $this->userRepository->update($user, $data);

        if (!$result) {
            throw new UserUpdateQueryException('Ошибка при обновлении пользователя!', 500);
        }

        return $this->userRepository->findById($user->id);
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
