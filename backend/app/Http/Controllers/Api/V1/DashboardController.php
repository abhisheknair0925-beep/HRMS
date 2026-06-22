<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\LeaveRequest;
use App\Traits\ApiResponseTrait;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function admin(): JsonResponse
    {
        $today = CarbonImmutable::today();
        $totalEmployees = Employee::count();
        $presentToday = AttendanceLog::whereDate('log_date', $today)
            ->whereIn('status', ['Present', 'Late', 'Half-Day'])
            ->distinct('employee_id')
            ->count('employee_id');

        $departmentAllocation = Department::query()
            ->withCount(['employees as employee_count'])
            ->orderBy('name')
            ->get()
            ->map(fn (Department $department) => [
                'name' => $department->name,
                'count' => $department->employee_count,
                'percentage' => $totalEmployees > 0 ? round(($department->employee_count / $totalEmployees) * 100) : 0,
            ])
            ->values();

        return $this->successResponse([
            'stats' => [
                'total_employees' => $totalEmployees,
                'present_today' => $presentToday,
                'absent_today' => max($totalEmployees - $presentToday, 0),
                'pending_leaves' => LeaveRequest::where('status', 'Pending')->count(),
                'birthdays_this_month' => Employee::whereMonth('dob', $today->month)->count(),
                'monthly_payroll' => $this->estimateMonthlyPayroll(),
            ],
            'attendance_trends' => $this->attendanceTrend($today),
            'department_allocation' => $departmentAllocation,
        ], 'Admin dashboard retrieved.');
    }

    public function hr(): JsonResponse
    {
        $today = CarbonImmutable::today();
        $monthStart = $today->startOfMonth();
        $weekEnd = $today->endOfWeek();

        return $this->successResponse([
            'stats' => [
                'active_staff' => Employee::whereIn('status', ['Active', 'Probation'])->count(),
                'new_joiners_month' => Employee::whereBetween('joining_date', [$monthStart, $today])->count(),
                'birthdays_this_week' => Employee::whereNotNull('dob')
                    ->get()
                    ->filter(fn (Employee $employee) => $this->birthdayFallsBetween($employee, $today, $weekEnd))
                    ->count(),
                'pending_leaves' => LeaveRequest::where('status', 'Pending')->count(),
            ],
            'onboarding_queue' => $this->onboardingQueue($today),
            'document_verification' => $this->documentVerificationQueue(),
        ], 'HR dashboard retrieved.');
    }

    private function attendanceTrend(CarbonImmutable $today): Collection
    {
        return collect(range(4, 0))
            ->map(function (int $daysAgo) use ($today) {
                $date = $today->subDays($daysAgo);
                $logs = AttendanceLog::whereDate('log_date', $date)->get();

                return [
                    'day' => $date->format('D'),
                    'Present' => $logs->whereIn('status', ['Present', 'Late', 'Half-Day'])->count(),
                    'Late' => $logs->where('status', 'Late')->count(),
                ];
            })
            ->values();
    }

    private function onboardingQueue(CarbonImmutable $today): Collection
    {
        return Employee::query()
            ->withCount('documents')
            ->with(['designation'])
            ->whereBetween('joining_date', [$today->subDays(90), $today])
            ->latest('joining_date')
            ->limit(5)
            ->get()
            ->map(function (Employee $employee) {
                $hasUser = !empty($employee->user_id);
                $hasShift = !empty($employee->shift_id);
                $hasDocuments = $employee->documents_count > 0;
                $completedSteps = collect([$hasUser, $hasShift, $hasDocuments])->filter()->count();

                $step = match (false) {
                    $hasUser => 'Create user account',
                    $hasShift => 'Assign shift roster',
                    $hasDocuments => 'Collect joining documents',
                    default => 'Final HR review',
                };

                return [
                    'id' => $employee->id,
                    'name' => trim("{$employee->first_name} {$employee->last_name}"),
                    'step' => $step,
                    'percent' => (int) round(($completedSteps / 3) * 100),
                    'designation' => $employee->designation?->title,
                ];
            });
    }

    private function documentVerificationQueue(): Collection
    {
        return EmployeeDocument::query()
            ->with('employee')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (EmployeeDocument $document) => [
                'id' => $document->id,
                'name' => $document->employee
                    ? trim("{$document->employee->first_name} {$document->employee->last_name}")
                    : 'Employee',
                'doc' => $document->name,
                'status' => 'Pending Review',
            ]);
    }

    private function birthdayFallsBetween(Employee $employee, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        $birthday = CarbonImmutable::parse($employee->dob)->setYear($start->year);

        return $birthday->betweenIncluded($start, $end);
    }

    private function estimateMonthlyPayroll(): int
    {
        $gradeAmounts = [
            'Grade L1' => 12000,
            'Grade L2' => 9000,
            'Grade L3' => 7000,
            'Grade L4' => 4800,
            'Grade L5' => 3500,
        ];

        return Employee::query()
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->select('designations.salary_grade', DB::raw('count(employees.id) as employee_count'))
            ->groupBy('designations.salary_grade')
            ->get()
            ->sum(fn ($row) => ($gradeAmounts[$row->salary_grade] ?? 4000) * (int) $row->employee_count);
    }
}
