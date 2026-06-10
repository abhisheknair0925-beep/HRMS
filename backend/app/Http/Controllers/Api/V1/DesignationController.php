<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of designations.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $designations = Designation::with('department')->get();
        return $this->successResponse($designations, 'Designations list retrieved.');
    }

    /**
     * Store a newly created designation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|uuid|exists:departments,id',
            'title' => 'required|string|max:255',
            'salary_grade' => 'nullable|string|max:50',
        ]);

        $designation = Designation::create($validated);

        return $this->successResponse($designation, 'Designation created successfully.', 201);
    }

    /**
     * Display the specified designation.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $designation = Designation::with('department')->find($id);

        if (!$designation) {
            return $this->errorResponse('Designation not found.', 404);
        }

        return $this->successResponse($designation, 'Designation details retrieved.');
    }

    /**
     * Update the specified designation.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $designation = Designation::find($id);

        if (!$designation) {
            return $this->errorResponse('Designation not found.', 404);
        }

        $validated = $request->validate([
            'department_id' => 'sometimes|required|uuid|exists:departments,id',
            'title' => 'sometimes|required|string|max:255',
            'salary_grade' => 'sometimes|nullable|string|max:50',
        ]);

        $designation->update($validated);

        return $this->successResponse($designation, 'Designation updated successfully.');
    }

    /**
     * Remove the specified designation.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $designation = Designation::find($id);

        if (!$designation) {
            return $this->errorResponse('Designation not found.', 404);
        }

        $designation->delete();

        return $this->successResponse(null, 'Designation deleted successfully.');
    }
}
