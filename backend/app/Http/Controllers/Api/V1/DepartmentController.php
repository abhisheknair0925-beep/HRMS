<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of departments.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $departments = Department::with('manager')->get();
        return $this->successResponse($departments, 'Departments list retrieved.');
    }

    /**
     * Store a newly created department.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|uuid|exists:users,id',
        ]);

        $department = Department::create($validated);

        return $this->successResponse($department, 'Department created successfully.', 201);
    }

    /**
     * Display the specified department.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $department = Department::with('manager')->find($id);

        if (!$department) {
            return $this->errorResponse('Department not found.', 404);
        }

        return $this->successResponse($department, 'Department details retrieved.');
    }

    /**
     * Update the specified department.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $department = Department::find($id);

        if (!$department) {
            return $this->errorResponse('Department not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'manager_id' => 'sometimes|nullable|uuid|exists:users,id',
        ]);

        $department->update($validated);

        return $this->successResponse($department, 'Department updated successfully.');
    }

    /**
     * Remove the specified department.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $department = Department::find($id);

        if (!$department) {
            return $this->errorResponse('Department not found.', 404);
        }

        $department->delete();

        return $this->successResponse(null, 'Department deleted successfully.');
    }
}
