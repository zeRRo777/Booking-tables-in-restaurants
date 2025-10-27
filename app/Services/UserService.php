<?php

namespace App\Services;

use App\DTOs\Contracts\UpdateUserDtoInterface;
use App\DTOs\User\AddUserRoleDTO;
use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UserFilterDTO;
use App\Exceptions\NotFoundException;
use App\Exceptions\UserNotHaveRoleException;
use App\Http\Requests\StoreUserRoleRequest;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected RoleRepositoryInterface $roleRepository,
        protected RoleService $roleService,
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

    public function deleteUser(int $idUser, bool $real = false): void
    {
        $user = $this->getUser($idUser);

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
            throw new NotFoundException('Пользователь не найден!');
        }

        return $user;
    }

    public function addRole(int $idUser, AddUserRoleDTO $dto): void
    {
        $user = $this->getUser($idUser);
        $role = $this->roleRepository->findByName($dto->name);

        if (!$role) {
            throw new NotFoundException('Роль не найдена!');
        }

        DB::transaction(function () use ($user, $role): void {
            $user->roles()->syncWithoutDetaching([$role->id]);
        });
    }

    public function removeRole(int $idUser, int $idRole): void
    {
        $user = $this->getUser($idUser);
        $role = $this->roleService->findRoleById($idRole);

        if (!$user->hasRole($role->name)) {
            throw new UserNotHaveRoleException('Пользователь не имеет данной роли!');
        }

        DB::transaction(function () use ($user, $role): void {
            $user->roles()->detach($role);
        });
    }
}
