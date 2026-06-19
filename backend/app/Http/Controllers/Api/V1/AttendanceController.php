<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Models\AttendanceLog;
use App\Models\AttendanceRegularization;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected AttendanceService $attendanceService) {}

    /**
     * Trigger employee check-in log.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clockIn(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $validated = $request->validate([
            'employee_id' => 'sometimes|required|uuid|exists:employees,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $employeeId = $request->input('employee_id');
        if (!$employeeId) {
            $userEmployee = $user->employee;
            if (!$userEmployee) {
                return $this->errorResponse('Employee profile not found.', 404);
            }
            $employeeId = $userEmployee->id;
        } else {
            // Standard employees can only check in for themselves
            if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
                $userEmployee = $user->employee;
                if (!$userEmployee || $userEmployee->id !== $employeeId) {
                    return $this->errorResponse('Access denied. You can only record attendance for yourself.', 403);
                }
            }
        }

        $log = $this->attendanceService->clockIn(
            $employeeId,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $request->ip() ?? '127.0.0.1'
        );

        return $this->successResponse($log, 'Clocked in successfully.', 201);
    }

    /**
     * Trigger employee check-out log.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clockOut(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $validated = $request->validate([
            'employee_id' => 'sometimes|required|uuid|exists:employees,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $employeeId = $request->input('employee_id');
        if (!$employeeId) {
            $userEmployee = $user->employee;
            if (!$userEmployee) {
                return $this->errorResponse('Employee profile not found.', 404);
            }
            $employeeId = $userEmployee->id;
        } else {
            // Standard employees can only check out for themselves
            if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
                $userEmployee = $user->employee;
                if (!$userEmployee || $userEmployee->id !== $employeeId) {
                    return $this->errorResponse('Access denied. You can only record attendance for yourself.', 403);
                }
            }
        }

        $log = $this->attendanceService->clockOut(
            $employeeId,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $request->ip() ?? '127.0.0.1'
        );

        return $this->successResponse($log, 'Clocked out successfully.');
    }

    /**
     * Submit correction/regularization request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function regularize(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $validated = $request->validate([
            'employee_id' => 'sometimes|required|uuid|exists:employees,id',
            'requested_date' => 'required|date',
            'requested_clock_in' => 'nullable|date_format:H:i:s',
            'requested_clock_out' => 'nullable|date_format:H:i:s',
            'reason' => 'required|string|max:500',
        ]);

        $employeeId = $request->input('employee_id');
        if (!$employeeId) {
            $userEmployee = $user->employee;
            if (!$userEmployee) {
                return $this->errorResponse('Employee profile not found.', 404);
            }
            $employeeId = $userEmployee->id;
        } else {
            // Standard employees can only regularize for themselves
            if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
                $userEmployee = $user->employee;
                if (!$userEmployee || $userEmployee->id !== $employeeId) {
                    return $this->errorResponse('Access denied. You can only submit attendance corrections for yourself.', 403);
                }
            }
        }

        $regularization = $this->attendanceService->requestRegularization(
            $employeeId,
            $validated['requested_date'],
            $validated['requested_clock_in'] ?? null,
            $validated['requested_clock_out'] ?? null,
            $validated['reason']
        );

        return $this->successResponse($regularization, 'Regularization request submitted.', 201);
    }

    /**
     * Retrieve all pending regularization requests.
     *
     * @return JsonResponse
     */
    public function regularizationIndex(): JsonResponse
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            return $this->errorResponse('Access denied. Only Admins, HR, or Managers can view pending corrections.', 403);
        }

        $requests = AttendanceRegularization::with('employee')->where('status', 'Pending')->get();
        return $this->successResponse($requests, 'Pending correction requests retrieved.');
    }

    /**
     * Process regularization approval.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function approveRegularization(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            return $this->errorResponse('Access denied. Only Admins, HR, or Managers can approve corrections.', 403);
        }

        $regularizationRequest = AttendanceRegularization::find($id);
        if (!$regularizationRequest || $regularizationRequest->company_id !== $user->company_id) {
            return $this->errorResponse('Regularization request not found or unauthorized.', 404);
        }

        $approverId = $user->id;
        
        $regularization = $this->attendanceService->approveRegularization($id, $approverId);

        return $this->successResponse($regularization, 'Attendance regularization approved and log updated.');
    }

    /**
     * Retrieve attendance logs metrics report.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function report(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $request->validate([
            'employee_id' => 'sometimes|required|uuid|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // If querying a specific employee, standard employees can only query themselves
        if ($request->employee_id && !$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $request->employee_id) {
                return $this->errorResponse('Access denied. You can only view your own attendance metrics.', 403);
            }
        }

        // If no employee_id is supplied, standard employees are forced to query themselves
        if (!$request->employee_id && !$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            $userEmployee = $user->employee;
            if (!$userEmployee) {
                return $this->errorResponse('Access denied.', 403);
            }
            $request->merge(['employee_id' => $userEmployee->id]);
        }

        $query = AttendanceLog::with('employee')
            ->whereBetween('log_date', [$request->start_date, $request->end_date]);

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        $logs = $query->get();

        // Calculate summary statistics
        $summary = [
            'total_present' => $logs->whereIn('status', ['Present', 'Late'])->count(),
            'total_late' => $logs->where('status', 'Late')->count(),
            'total_half_day' => $logs->where('status', 'Half-Day')->count(),
            'total_absent' => $logs->where('status', 'Absent')->count(),
            'total_overtime_minutes' => $logs->sum('overtime_minutes'),
            'logs' => $logs
        ];

        return $this->successResponse($summary, 'Attendance metrics resolved.');
    }
}
