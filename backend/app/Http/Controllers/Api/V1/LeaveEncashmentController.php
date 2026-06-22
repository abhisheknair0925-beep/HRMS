<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LeaveService;
use App\Models\LeaveEncashment;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveEncashmentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected LeaveService $leaveService) {}

    /**
     * Submit a new leave encashment request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function requestEncashment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'sometimes|required|uuid|exists:employees,id',
            'leave_policy_id' => 'required|uuid|exists:leave_policies,id',
            'days_to_encash' => 'required|numeric|min:0.5',
            'amount_per_day' => 'required|numeric|min:0',
        ]);

        $employeeId = $validated['employee_id'] ?? $request->user()?->employee?->id;

        if (!$employeeId) {
            return $this->errorResponse('Employee profile not found.', 422);
        }

        $encashment = $this->leaveService->requestEncashment(
            $employeeId,
            $validated['leave_policy_id'],
            (float) $validated['days_to_encash'],
            (float) $validated['amount_per_day']
        );

        return $this->successResponse($encashment, 'Leave encashment request submitted successfully.', 201);
    }

    /**
     * Approve a pending leave encashment.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function approveEncashment(Request $request, string $id): JsonResponse
    {
        $approverId = $request->user()?->id ?? '00000000-0000-0000-0000-000000000000'; // Admin fallback
        
        $encashment = $this->leaveService->approveEncashment($id, $approverId)->load(['employee', 'leavePolicy']);

        return $this->successResponse($encashment, 'Encashment approved and balance updated successfully.');
    }

    /**
     * Reject a pending leave encashment.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function rejectEncashment(Request $request, string $id): JsonResponse
    {
        $approverId = $request->user()?->id ?? '00000000-0000-0000-0000-000000000000'; // Admin fallback

        $encashment = $this->leaveService->rejectEncashment($id, $approverId)->load(['employee', 'leavePolicy']);

        return $this->successResponse($encashment, 'Encashment rejected successfully.');
    }

    /**
     * Display listing of pending encashments.
     *
     * @return JsonResponse
     */
    public function indexPending(): JsonResponse
    {
        $requests = LeaveEncashment::with(['employee', 'leavePolicy'])
            ->where('status', 'Pending')
            ->get();

        return $this->successResponse($requests, 'Pending encashment list retrieved.');
    }
}
