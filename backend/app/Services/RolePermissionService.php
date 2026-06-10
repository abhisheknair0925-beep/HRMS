<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Exceptions\BusinessException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;

class RolePermissionService
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository,
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get all roles.
     *
     * @return Collection
     */
    public function getRoles(): Collection
    {
        return $this->roleRepository->all();
    }

    /**
     * Create a new role.
     *
     * @param string $name
     * @param string $guardName
     * @return Role
     * @throws BusinessException
     */
    public function createRole(string $name, string $guardName = 'api'): Role
    {
        $existing = $this->roleRepository->findByName($name);
        if ($existing) {
            throw new BusinessException("A role named '{$name}' already exists in the system.", 422);
        }

        $role = $this->roleRepository->create([
            'name' => $name,
            'guard_name' => $guardName
        ]);

        activity()
            ->performedOn($role)
            ->withProperties(['role_name' => $name])
            ->log('System role created');

        return $role;
    }

    /**
     * Delete a role.
     *
     * @param string $id
     * @return void
     * @throws BusinessException
     */
    public function deleteRole(string $id): void
    {
        $role = $this->roleRepository->findById($id);
        if (!$role) {
            throw new BusinessException("Role not found.", 404);
        }

        if (in_array($role->name, ['Super Admin', 'Company Admin', 'Employee'], true)) {
            throw new BusinessException("Default system roles cannot be deleted.", 403);
        }

        $this->roleRepository->delete($id);

        activity()
            ->performedOn($role)
            ->withProperties(['role_name' => $role->name])
            ->log('System role deleted');
    }

    /**
     * Assign a Spatie role to a user.
     *
     * @param string $userId
     * @param string $roleName
     * @return void
     * @throws BusinessException
     */
    public function assignRoleToUser(string $userId, string $roleName): void
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new BusinessException("User profile not found.", 404);
        }

        $role = $this->roleRepository->findByName($roleName);
        if (!$role) {
            throw new BusinessException("Role '{$roleName}' does not exist.", 404);
        }

        $user->assignRole($roleName);

        activity()
            ->performedOn($user)
            ->withProperties(['role' => $roleName, 'user_name' => $user->name])
            ->log('Assigned role to user');
    }

    /**
     * Sync permissions to a specific role.
     *
     * @param string $roleId
     * @param array $permissions
     * @return void
     * @throws BusinessException
     */
    public function syncPermissionsToRole(string $roleId, array $permissions): void
    {
        $role = $this->roleRepository->findById($roleId);
        if (!$role) {
            throw new BusinessException("Role not found.", 404);
        }

        // Validate permissions existence
        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if (!$permission) {
                // Proactively create permission if it doesn't exist for flexibility
                Permission::create(['name' => $permissionName, 'guard_name' => $role->guard_name]);
            }
        }

        $role->syncPermissions($permissions);

        activity()
            ->performedOn($role)
            ->withProperties(['permissions' => $permissions])
            ->log('Synced permissions to role');
    }
}
