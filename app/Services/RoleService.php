<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {}

    public function getAllRoles(): Collection
    {
        return $this->roleRepository->all();
    }

    public function createRole(string $name): Role
    {
        return $this->roleRepository->create($name);
    }

    public function updateRole(int $id, string $name): Role
    {
        $role = $this->findRoleById($id);

        return $this->roleRepository->update($role, $name);
    }

    public function deleteRole(int $id): void
    {
        $role = $this->findRoleById($id);

        $this->roleRepository->delete($role, true);
    }

    public function findRoleById(int $id): Role
    {
        $role = $this->roleRepository->findById($id);

        if (!$role) {
            throw new NotFoundException("Роль с ID {$id} не найдена.");
        }

        return $role;
    }
}
