<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\RolePermissionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected RolePermissionService $rolePermissionService) {}

    /**
     * Display listing of system roles.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $roles = $this->rolePermissionService->getRoles();
        return $this->successResponse($roles, 'Roles list retrieved.');
    }

    /**
     * Create a new role.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'guard_name' => 'sometimes|string|in:api,web',
        ]);

        $role = $this->rolePermissionService->createRole(
            $validated['name'],
            $validated['guard_name'] ?? 'api'
        );

        return $this->successResponse($role, 'Role created successfully.', 201);
    }

    /**
     * Remove a role from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $this->rolePermissionService->deleteRole($id);
        return $this->successResponse(null, 'Role deleted successfully.');
    }

    /**
     * Assign role to user profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function assignRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
            'role_name' => 'required|string',
        ]);

        $this->rolePermissionService->assignRoleToUser(
            $validated['user_id'],
            $validated['role_name']
        );

        return $this->successResponse(null, 'Role assigned successfully.');
    }

    /**
     * Sync permissions to a specific role.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function syncPermissions(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        $this->rolePermissionService->syncPermissionsToRole(
            $id,
            $validated['permissions']
        );

        return $this->successResponse(null, 'Permissions synced successfully.');
    }
}
