<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use Spatie\Permission\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function all(): Collection
    {
        return Role::all();
    }

    public function findById(string $id): ?Role
    {
        return Role::where('id', $id)->first();
    }

    public function findByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $role = $this->findById($id);
        return $role ? $role->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $role = $this->findById($id);
        return $role ? $role->delete() : false;
    }
}
