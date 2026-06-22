<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Shift;
use App\Services\LeaveService;
use App\Traits\ApiResponseTrait;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ManagerDashboardController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly LeaveService $leaveService) {}

    public function dashboard(Request $request): JsonResponse
    {
        $managerId = $request->user()?->id;

        if (!$managerId) {
            return $this->errorResponse('Manager profile not found.', 404);
        }

        $team = $this->directReports($managerId);
        $teamIds = $team->pluck('id')->all();
        $pendingLeaves = $this->teamLeaveRequests($teamIds);

        return $this->successResponse([
            'team' => $this->formatTeam($team),
            'leave_requests' => $pendingLeaves,
            'shifts' => Shift::orderBy('name')->get(['id', 'name', 'start_time', 'end_time']),
            'summary' => [
                'checked_in_count' => $team->whereIn('today_status', ['Present', 'Late'])->count(),
                'total_team_count' => $team->count(),
                'avg_weekly_hours' => $this->averageWeeklyHours($team),
                'pending_leave_requests' => $pendingLeaves->where('status', 'Pending')->count(),
            ],
        ], 'Manager dashboard retrieved.');
    }

    public function approveLeave(Request $request, string $id): JsonResponse
    {
        if (!$this->canManageLeave($request, $id)) {
            return $this->errorResponse('Leave request not found for your direct reports.', 404);
        }

        $leave = $this->leaveService->approveLeave($id, (string) $request->user()->id)
            ->load(['employee', 'leavePolicy']);

        return $this->successResponse($this->formatLeaveRequest($leave), 'Team leave request approved.');
    }

    public function rejectLeave(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        if (!$this->canManageLeave($request, $id)) {
            return $this->errorResponse('Leave request not found for your direct reports.', 404);
        }

        $leave = $this->leaveService->rejectLeave(
            $id,
            (string) $request->user()->id,
            $validated['rejection_reason'] ?? 'Rejected by reporting manager.'
        )->load(['employee', 'leavePolicy']);

        return $this->successResponse($this->formatLeaveRequest($leave), 'Team leave request rejected.');
    }

    public function updateShift(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'shift_id' => 'required|uuid|exists:shifts,id',
        ]);

        $shift = Shift::find($validated['shift_id']);
        if (!$shift) {
            return $this->errorResponse('Shift not found.', 404);
        }

        $employee = Employee::where('manager_id', $request->user()->id)
            ->where('id', $employeeId)
            ->first();

        if (!$employee) {
            return $this->errorResponse('Direct report not found.', 404);
        }

        $employee->update(['shift_id' => $validated['shift_id']]);
        $employee->load(['designation', 'shift']);

        return $this->successResponse($this->formatTeamMember($employee), 'Direct report shift updated.');
    }

    private function directReports(string $managerId): Collection
    {
        $today = CarbonImmutable::today();

        return Employee::query()
            ->with(['designation', 'shift'])
            ->with(['attendanceLogs' => fn ($query) => $query->whereDate('log_date', $today)])
            ->where('manager_id', $managerId)
            ->orderBy('first_name')
            ->get()
            ->map(function (Employee $employee) {
                $todayLog = $employee->attendanceLogs->first();
                $employee->setAttribute('today_status', $todayLog?->status ?? 'Not Checked-In');
                $employee->setAttribute('clock_in_time', $todayLog?->clock_in?->format('h:i A'));

                return $employee;
            });
    }

    private function formatTeam(Collection $team): Collection
    {
        return $team->map(fn (Employee $employee) => $this->formatTeamMember($employee))->values();
    }

    private function formatTeamMember(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'name' => trim("{$employee->first_name} {$employee->last_name}"),
            'role' => $employee->designation?->title ?? 'Team Member',
            'shift_id' => $employee->shift_id,
            'shift' => $employee->shift?->name ?? 'Unassigned',
            'todayStatus' => $employee->getAttribute('today_status') ?? 'Not Checked-In',
            'clockInTime' => $employee->getAttribute('clock_in_time'),
            'timesheet' => $this->weeklyTimesheet($employee->id),
        ];
    }

    private function teamLeaveRequests(array $teamIds): Collection
    {
        if (empty($teamIds)) {
            return collect();
        }

        return LeaveRequest::query()
            ->with(['employee', 'leavePolicy'])
            ->whereIn('employee_id', $teamIds)
            ->where('status', 'Pending')
            ->latest('created_at')
            ->get()
            ->map(fn (LeaveRequest $leave) => $this->formatLeaveRequest($leave))
            ->values();
    }

    private function formatLeaveRequest(LeaveRequest $leave): array
    {
        return [
            'id' => $leave->id,
            'employee_name' => $leave->employee
                ? trim("{$leave->employee->first_name} {$leave->employee->last_name}")
                : 'Employee',
            'policy_name' => $leave->leavePolicy?->name ?? 'Leave',
            'start_date' => $leave->start_date?->toDateString(),
            'end_date' => $leave->end_date?->toDateString(),
            'days' => (float) $leave->total_days,
            'reason' => $leave->reason,
            'status' => $leave->status,
        ];
    }

    private function weeklyTimesheet(string $employeeId): array
    {
        $weekStart = CarbonImmutable::today()->startOfWeek();
        $logs = AttendanceLog::where('employee_id', $employeeId)
            ->whereBetween('log_date', [$weekStart, $weekStart->addDays(4)])
            ->get()
            ->keyBy(fn (AttendanceLog $log) => $log->log_date->format('D'));

        return collect(['Mon', 'Tue', 'Wed', 'Thu', 'Fri'])
            ->mapWithKeys(fn (string $day) => [
                $day => round(((int) ($logs[$day]?->working_minutes ?? 0)) / 60, 1),
            ])
            ->all();
    }

    private function averageWeeklyHours(Collection $team): float
    {
        if ($team->isEmpty()) {
            return 0.0;
        }

        $totalHours = $team->sum(fn (Employee $employee) => array_sum($this->weeklyTimesheet($employee->id)));

        return round($totalHours / $team->count(), 1);
    }

    private function canManageLeave(Request $request, string $leaveId): bool
    {
        return LeaveRequest::query()
            ->where('id', $leaveId)
            ->whereHas('employee', fn ($query) => $query->where('manager_id', $request->user()->id))
            ->exists();
    }
}
