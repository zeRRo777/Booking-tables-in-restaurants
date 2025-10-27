<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function all(): Collection
    {
        return Role::all();
    }

    public function create(string $name): Role
    {
        return Role::create(['name' => $name]);
    }

    public function delete(Role $role, bool $real = false): bool
    {
        return $real ? $role->forceDelete() : $role->delete();
    }

    public function update(Role $role, string $name): Role
    {
        $role->name = $name;

        $role->save();

        return $role;
    }

    public function findById(int $id): Role|null
    {
        return Role::find($id);
    }

    public function findByName(string $name): Role|null
    {
        return Role::where('name', $name)->first();
    }
}
