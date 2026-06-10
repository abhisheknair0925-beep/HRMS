<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LeaveService;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected LeaveService $leaveService) {}

    /**
     * Submit a new leave request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function apply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'leave_policy_id' => 'required|uuid|exists:leave_policies,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'half_day' => 'sometimes|boolean',
            'reason' => 'required|string|max:1000',
        ]);

        $leave = $this->leaveService->applyForLeave(
            $validated['employee_id'],
            $validated['leave_policy_id'],
            $validated['start_date'],
            $validated['end_date'],
            (bool) ($validated['half_day'] ?? false),
            $validated['reason']
        );

        return $this->successResponse($leave, 'Leave application submitted successfully.', 201);
    }

    /**
     * Approve a pending leave request.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $approverId = $request->user()?->id ?? '00000000-0000-0000-0000-000000000000'; // Admin fallback
        
        $leave = $this->leaveService->approveLeave($id, $approverId);

        return $this->successResponse($leave, 'Leave request has been approved successfully.');
    }

    /**
     * Reject a pending leave request.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $approverId = $request->user()?->id ?? '00000000-0000-0000-0000-000000000000'; // Admin fallback

        $leave = $this->leaveService->rejectLeave($id, $approverId, $validated['rejection_reason']);

        return $this->successResponse($leave, 'Leave request has been rejected.');
    }

    /**
     * Display listing of pending requests.
     *
     * @return JsonResponse
     */
    public function pendingList(): JsonResponse
    {
        $requests = LeaveRequest::with(['employee', 'leavePolicy'])
            ->where('status', 'Pending')
            ->get();

        return $this->successResponse($requests, 'Pending leave requests list retrieved.');
    }

    /**
     * Display leave balances for specific employee.
     *
     * @param string $employeeId
     * @return JsonResponse
     */
    public function balances(string $employeeId): JsonResponse
    {
        $balances = LeaveBalance::with('leavePolicy')
            ->where('employee_id', $employeeId)
            ->get();

        return $this->successResponse($balances, 'Employee leave balances retrieved.');
    }
}
