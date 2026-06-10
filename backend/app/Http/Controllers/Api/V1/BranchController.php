<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the company's branches.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $branches = Branch::all();
        return $this->successResponse($branches, 'Branches list retrieved.');
    }

    /**
     * Store a newly created branch in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        // company_id is automatically assigned by BelongsToTenant trait boot creating hook
        $branch = Branch::create($validated);

        return $this->successResponse($branch, 'Branch created successfully.', 201);
    }

    /**
     * Display the specified branch.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return $this->errorResponse('Branch not found or unauthorized access.', 404);
        }

        return $this->successResponse($branch, 'Branch details retrieved.');
    }

    /**
     * Update the specified branch in storage.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return $this->errorResponse('Branch not found or unauthorized access.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $branch->update($validated);

        return $this->successResponse($branch, 'Branch updated successfully.');
    }

    /**
     * Remove the specified branch from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return $this->errorResponse('Branch not found or unauthorized access.', 404);
        }

        $branch->delete();

        return $this->successResponse(null, 'Branch deleted successfully.');
    }
}
