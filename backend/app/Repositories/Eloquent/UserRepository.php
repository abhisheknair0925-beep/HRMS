<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        // Use withoutGlobalScopes if we want global access to find by email during login or specific auth workflows
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $user = $this->findById($id);
        return $user ? $user->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $user = $this->findById($id);
        return $user ? $user->delete() : false;
    }
}
