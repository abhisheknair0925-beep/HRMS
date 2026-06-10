<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    public function all(): Collection;
    public function findById(string $id): ?Role;
    public function findByName(string $name): ?Role;
    public function create(array $data): Role;
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
}
