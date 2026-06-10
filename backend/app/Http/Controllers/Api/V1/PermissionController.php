<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display listing of permissions.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::all();
        return $this->successResponse($permissions, 'Permissions list retrieved.');
    }
}
