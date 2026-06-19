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
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'leave_policy_id' => 'required|uuid|exists:leave_policies,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'half_day' => 'sometimes|boolean',
            'reason' => 'required|string|max:1000',
        ]);

        // Standard employees can only apply for themselves
        if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $validated['employee_id']) {
                return $this->errorResponse('Access denied. You can only apply for leave for yourself.', 403);
            }
        }

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
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            return $this->errorResponse('Access denied. Only Admins, HR, or Managers can approve leaves.', 403);
        }

        // Validate that leave request belongs to the same tenant
        $leaveRequest = LeaveRequest::find($id);
        if (!$leaveRequest || $leaveRequest->company_id !== $user->company_id) {
            return $this->errorResponse('Leave request not found or unauthorized.', 404);
        }

        $approverId = $user->id;
        
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
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            return $this->errorResponse('Access denied. Only Admins, HR, or Managers can reject leaves.', 403);
        }

        // Validate that leave request belongs to the same tenant
        $leaveRequest = LeaveRequest::find($id);
        if (!$leaveRequest || $leaveRequest->company_id !== $user->company_id) {
            return $this->errorResponse('Leave request not found or unauthorized.', 404);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $approverId = $user->id;

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
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            return $this->errorResponse('Access denied. Only Admins, HR, or Managers can view pending leaves.', 403);
        }

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
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $employee = Employee::find($employeeId);
        if (!$employee || $employee->company_id !== $user->company_id) {
            return $this->errorResponse('Employee not found or access denied.', 404);
        }

        if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $employeeId) {
                return $this->errorResponse('Access denied. You can only view your own leave balances.', 403);
            }
        }

        $balances = LeaveBalance::with('leavePolicy')
            ->where('employee_id', $employeeId)
            ->get();

        return $this->successResponse($balances, 'Employee leave balances retrieved.');
    }
}
