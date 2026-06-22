<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define guards to seed
        $guards = ['web', 'api'];

        // Define permissions
        $permissions = [
            'view_dashboard',
            'manage_settings',
            'manage_employees',
            'manage_departments',
            'manage_designations',
            'manage_shifts',
            'manage_attendance',
            'manage_leaves',
            'apply_leaves',
            'approve_leaves',
            'manage_announcements',
            'view_reports',
        ];

        // Create permissions for each guard
        foreach ($guards as $guard) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, $guard);
            }
        }

        // Define roles and their corresponding permissions
        $rolePermissions = [
            'Super Admin' => $permissions,
            'Company Admin' => $permissions,
            'Admin' => $permissions,
            'HR' => [
                'view_dashboard',
                'manage_employees',
                'manage_departments',
                'manage_designations',
                'manage_shifts',
                'manage_attendance',
                'manage_leaves',
                'apply_leaves',
                'approve_leaves',
                'manage_announcements',
                'view_reports',
            ],
            'Manager' => [
                'view_dashboard',
                'manage_attendance',
                'apply_leaves',
                'approve_leaves',
                'view_reports',
            ],
            'Employee' => [
                'view_dashboard',
                'apply_leaves',
            ],
        ];

        // Create roles and sync permissions for each guard
        foreach ($guards as $guard) {
            foreach ($rolePermissions as $roleName => $perms) {
                $role = Role::findOrCreate($roleName, $guard);
                $role->syncPermissions($perms);
            }
        }
    }
}
