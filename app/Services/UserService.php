<?php

namespace App\Services;

use App\DTOs\CreateUserDTO;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
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

    public function updateUser(User $user, array $data): User|bool
    {
        $result = $this->userRepository->update($user, $data);

        if (!$result) {
            throw new \Exception('Ошибка при обновлении пользователя!');
        }

        return $this->userRepository->findById($user->id);
    }
}
