<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    public function all(): Collection;

    public function create(string $name): Role;

    public function update(Role $role, string $name): Role;

    public function delete(Role $role, bool $real = false): bool;

    public function findById(int $id): ?Role;
    public function findByName(string $name): ?Role;
}
