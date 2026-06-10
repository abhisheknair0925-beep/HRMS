<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\LeaveBalance;
use App\Models\Announcement;
use App\Exceptions\BusinessException;
use Illuminate\Support\Collection;

class EssService
{
    /**
     * Retrieve data for the employee self-service dashboard.
     *
     * @param string $employeeId
     * @return array
     * @throws BusinessException
     */
    public function getDashboardData(string $employeeId): array
    {
        $employee = Employee::with(['department', 'designation', 'shift', 'user'])->find($employeeId);
        if (!$employee) {
            throw new BusinessException("Employee profile not found.", 404);
        }

        $today = now()->toDateString();
        $todayAttendance = AttendanceLog::where('employee_id', $employeeId)
            ->where('log_date', $today)
            ->first();

        $leaveBalances = LeaveBalance::where('employee_id', $employeeId)
            ->with('leavePolicy')
            ->get();

        $announcements = Announcement::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'employee' => $employee,
            'today_attendance' => $todayAttendance,
            'leave_balances' => $leaveBalances,
            'announcements' => $announcements,
        ];
    }

    /**
     * Update employee personal details from ESS.
     *
     * @param string $employeeId
     * @param array $data
     * @return Employee
     * @throws BusinessException
     */
    public function updateProfile(string $employeeId, array $data): Employee
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            throw new BusinessException("Employee profile not found.", 404);
        }

        // Only allow updating contact information, emergency contacts, and bank details
        if (isset($data['phone'])) {
            $employee->phone = $data['phone'];
        }

        if (isset($data['personal_info'])) {
            $currentPersonal = $employee->personal_info ?? [];
            $employee->personal_info = array_merge($currentPersonal, $data['personal_info']);
        }

        if (isset($data['emergency_contacts'])) {
            $employee->emergency_contacts = $data['emergency_contacts'];
        }

        if (isset($data['bank_details'])) {
            $employee->bank_details = $data['bank_details'];
        }

        $employee->save();

        return $employee;
    }

    /**
     * Retrieve active announcements for a company.
     *
     * @param string $companyId
     * @return Collection
     */
    public function getAnnouncements(string $companyId): Collection
    {
        return Announcement::where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Retrieve list of mock payslips for this employee.
     *
     * @param string $employeeId
     * @return array
     */
    public function getPayslipsList(string $employeeId): array
    {
        // Return a mock payload representing past payslips
        $currentYear = (int) now()->format('Y');
        
        return [
            [
                'id' => 'payslip-' . $employeeId . '-' . $currentYear . '-05',
                'month_name' => 'May ' . $currentYear,
                'year' => $currentYear,
                'month' => 5,
                'basic_salary' => 5000.00,
                'allowances' => 1200.00,
                'deductions' => 200.00,
                'net_pay' => 6000.00,
                'status' => 'Paid',
            ],
            [
                'id' => 'payslip-' . $employeeId . '-' . $currentYear . '-04',
                'month_name' => 'April ' . $currentYear,
                'year' => $currentYear,
                'month' => 4,
                'basic_salary' => 5000.00,
                'allowances' => 1200.00,
                'deductions' => 200.00,
                'net_pay' => 6000.00,
                'status' => 'Paid',
            ],
            [
                'id' => 'payslip-' . $employeeId . '-' . $currentYear . '-03',
                'month_name' => 'March ' . $currentYear,
                'year' => $currentYear,
                'month' => 3,
                'basic_salary' => 5000.00,
                'allowances' => 1000.00,
                'deductions' => 200.00,
                'net_pay' => 5800.00,
                'status' => 'Paid',
            ],
        ];
    }
}
