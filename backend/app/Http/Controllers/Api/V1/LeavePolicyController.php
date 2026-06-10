<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeavePolicyController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of leave policies.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $policies = LeavePolicy::all();
        return $this->successResponse($policies, 'Leave policies list retrieved.');
    }

    /**
     * Store a newly created leave policy.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_days' => 'required|numeric|min:0',
            'carry_over_max' => 'sometimes|numeric|min:0',
            'accrual_rate' => 'nullable|string|in:monthly,quarterly,yearly',
        ]);

        $policy = LeavePolicy::create($validated);

        return $this->successResponse($policy, 'Leave policy created successfully.', 201);
    }

    /**
     * Display the specified leave policy.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $policy = LeavePolicy::find($id);

        if (!$policy) {
            return $this->errorResponse('Leave policy not found.', 404);
        }

        return $this->successResponse($policy, 'Leave policy details retrieved.');
    }

    /**
     * Update the specified leave policy.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $policy = LeavePolicy::find($id);

        if (!$policy) {
            return $this->errorResponse('Leave policy not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'total_days' => 'sometimes|required|numeric|min:0',
            'carry_over_max' => 'sometimes|required|numeric|min:0',
            'accrual_rate' => 'sometimes|nullable|string|in:monthly,quarterly,yearly',
        ]);

        $policy->update($validated);

        return $this->successResponse($policy, 'Leave policy updated successfully.');
    }

    /**
     * Remove the specified leave policy.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $policy = LeavePolicy::find($id);

        if (!$policy) {
            return $this->errorResponse('Leave policy not found.', 404);
        }

        $policy->delete();

        return $this->successResponse(null, 'Leave policy deleted successfully.');
    }
}
