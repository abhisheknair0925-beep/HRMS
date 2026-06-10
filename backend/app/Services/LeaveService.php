<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveEncashment;
use App\Exceptions\BusinessException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    /**
     * Submit a new leave request, validating balances.
     *
     * @param string $employeeId
     * @param string $policyId
     * @param string $startDate
     * @param string $endDate
     * @param bool $halfDay
     * @param string $reason
     * @return LeaveRequest
     * @throws BusinessException
     */
    public function applyForLeave(
        string $employeeId,
        string $policyId,
        string $startDate,
        string $endDate,
        bool $halfDay,
        string $reason
    ): LeaveRequest {
        return DB::transaction(function () use ($employeeId, $policyId, $startDate, $endDate, $halfDay, $reason) {
            $employee = Employee::find($employeeId);
            if (!$employee) {
                throw new BusinessException("Employee profile not found.", 404);
            }

            // 1. Calculate duration
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            
            if ($start->isAfter($end)) {
                throw new BusinessException("Start date cannot be after end date.", 422);
            }

            $totalDays = $halfDay ? 0.5 : (float) ($start->diffInDays($end) + 1);

            // 2. Fetch and lock Balance
            $balance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_policy_id', $policyId)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                throw new BusinessException("No leave balance registered for this policy.", 422);
            }

            $remaining = $balance->allocated_days - $balance->used_days - $balance->encashed_days;

            if ($remaining < $totalDays) {
                throw new BusinessException("Insufficient leave balance. Remaining: {$remaining} days, Requested: {$totalDays} days.", 422);
            }

            return LeaveRequest::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employeeId,
                'leave_policy_id' => $policyId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'half_day' => $halfDay,
                'total_days' => $totalDays,
                'reason' => $reason,
                'status' => 'Pending',
            ]);
        });
    }

    /**
     * Approve pending leave request.
     *
     * @param string $requestId
     * @param string $approverId
     * @return LeaveRequest
     * @throws BusinessException
     */
    public function approveLeave(string $requestId, string $approverId): LeaveRequest
    {
        return DB::transaction(function () use ($requestId, $approverId) {
            $request = LeaveRequest::lockForUpdate()->find($requestId);
            if (!$request) {
                throw new BusinessException("Leave request not found.", 404);
            }

            if ($request->status !== 'Pending') {
                throw new BusinessException("This request has already been processed.", 422);
            }

            // Lock and update balance
            $balance = LeaveBalance::where('employee_id', $request->employee_id)
                ->where('leave_policy_id', $request->leave_policy_id)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                throw new BusinessException("Employee leave balance profile not found.", 404);
            }

            $remaining = $balance->allocated_days - $balance->used_days - $balance->encashed_days;
            if ($remaining < $request->total_days) {
                // Auto reject if balance has become insufficient in the meantime
                $request->update([
                    'status' => 'Rejected',
                    'approved_by' => $approverId,
                    'rejection_reason' => 'Automatically rejected due to insufficient leave balance at approval time.',
                ]);
                throw new BusinessException("Insufficient balance to approve request. Automatically marked as Rejected.", 422);
            }

            // Deduct balance & approve
            $balance->increment('used_days', $request->total_days);
            $request->update([
                'status' => 'Approved',
                'approved_by' => $approverId,
            ]);

            activity()
                ->performedOn($request)
                ->withProperties(['approved_by' => $approverId, 'days' => $request->total_days])
                ->log('Leave request approved');

            return $request;
        });
    }

    /**
     * Reject pending leave request.
     *
     * @param string $requestId
     * @param string $approverId
     * @param string $reason
     * @return LeaveRequest
     * @throws BusinessException
     */
    public function rejectLeave(string $requestId, string $approverId, string $reason): LeaveRequest
    {
        $request = LeaveRequest::find($requestId);
        if (!$request) {
            throw new BusinessException("Leave request not found.", 404);
        }

        if ($request->status !== 'Pending') {
            throw new BusinessException("This request has already been processed.", 422);
        }

        $request->update([
            'status' => 'Rejected',
            'approved_by' => $approverId,
            'rejection_reason' => $reason,
        ]);

        activity()
            ->performedOn($request)
            ->withProperties(['rejected_by' => $approverId, 'reason' => $reason])
            ->log('Leave request rejected');

        return $request;
    }

    /**
     * Submit a leave encashment request.
     *
     * @param string $employeeId
     * @param string $policyId
     * @param float $days
     * @param float $amountPerDay
     * @return LeaveEncashment
     * @throws BusinessException
     */
    public function requestEncashment(string $employeeId, string $policyId, float $days, float $amountPerDay): LeaveEncashment
    {
        return DB::transaction(function () use ($employeeId, $policyId, $days, $amountPerDay) {
            $employee = Employee::find($employeeId);
            if (!$employee) {
                throw new BusinessException("Employee profile not found.", 404);
            }

            $balance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_policy_id', $policyId)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                throw new BusinessException("No leave balance profile registered.", 422);
            }

            $remaining = $balance->allocated_days - $balance->used_days - $balance->encashed_days;
            if ($remaining < $days) {
                throw new BusinessException("Insufficient balance for encashment. Remaining: {$remaining} days, Requested: {$days} days.", 422);
            }

            $totalAmount = $days * $amountPerDay;

            return LeaveEncashment::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employeeId,
                'leave_policy_id' => $policyId,
                'days_to_encash' => $days,
                'amount_per_day' => $amountPerDay,
                'total_amount' => $totalAmount,
                'status' => 'Pending',
            ]);
        });
    }

    /**
     * Approve and execute leave encashment.
     *
     * @param string $encashmentId
     * @param string $approverId
     * @return LeaveEncashment
     * @throws BusinessException
     */
    public function approveEncashment(string $encashmentId, string $approverId): LeaveEncashment
    {
        return DB::transaction(function () use ($encashmentId, $approverId) {
            $encashment = LeaveEncashment::lockForUpdate()->find($encashmentId);
            if (!$encashment) {
                throw new BusinessException("Encashment request not found.", 404);
            }

            if ($encashment->status !== 'Pending') {
                throw new BusinessException("This encashment request has already been processed.", 422);
            }

            $balance = LeaveBalance::where('employee_id', $encashment->employee_id)
                ->where('leave_policy_id', $encashment->leave_policy_id)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                throw new BusinessException("Employee leave balance profile not found.", 404);
            }

            $remaining = $balance->allocated_days - $balance->used_days - $balance->encashed_days;
            if ($remaining < $encashment->days_to_encash) {
                $encashment->update(['status' => 'Rejected', 'approved_by' => $approverId]);
                throw new BusinessException("Insufficient balance to complete encashment approval. Automatically marked as Rejected.", 422);
            }

            // Deduct balance & approve
            $balance->increment('encashed_days', $encashment->days_to_encash);
            
            $encashment->update([
                'status' => 'Approved',
                'approved_by' => $approverId,
            ]);

            activity()
                ->performedOn($encashment)
                ->withProperties(['approved_by' => $approverId, 'payout' => $encashment->total_amount])
                ->log('Leave encashment request approved');

            return $encashment;
        });
    }
}
