<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
}
