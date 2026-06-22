<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Acme Dashboard Corp',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Admin User',
            'email' => 'admin.dashboard@acme.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $department = Department::create([
            'company_id' => $this->company->id,
            'name' => 'Engineering',
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'department_id' => $department->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'joining_date' => now()->toDateString(),
            'dob' => now()->toDateString(),
            'status' => 'Active',
        ]);

        $secondEmployee = Employee::create([
            'company_id' => $this->company->id,
            'department_id' => $department->id,
            'first_name' => 'Jane',
            'last_name' => 'Staff',
            'joining_date' => now()->subDays(7)->toDateString(),
            'status' => 'Probation',
        ]);

        AttendanceLog::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'log_date' => now()->toDateString(),
            'clock_in' => now()->startOfDay()->addHours(9)->toDateTimeString(),
            'status' => 'Present',
        ]);

        $leavePolicy = LeavePolicy::create([
            'company_id' => $this->company->id,
            'name' => 'Casual Leave',
            'total_days' => 10.0,
        ]);

        LeaveRequest::create([
            'company_id' => $this->company->id,
            'employee_id' => $secondEmployee->id,
            'leave_policy_id' => $leavePolicy->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'total_days' => 1,
            'reason' => 'Personal work',
            'status' => 'Pending',
        ]);

        EmployeeDocument::create([
            'company_id' => $this->company->id,
            'employee_id' => $secondEmployee->id,
            'name' => 'Passport Scan',
            'type' => 'Passport',
            'file_url' => '/storage/passport.pdf',
        ]);
    }

    public function test_admin_dashboard_returns_aggregate_payload(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->getJson('/api/v1/dashboard/admin');

        $response->assertStatus(200)
            ->assertJsonPath('data.stats.total_employees', 2)
            ->assertJsonPath('data.stats.present_today', 1)
            ->assertJsonPath('data.stats.pending_leaves', 1)
            ->assertJsonStructure([
                'data' => [
                    'stats' => [
                        'total_employees',
                        'present_today',
                        'absent_today',
                        'pending_leaves',
                        'birthdays_this_month',
                        'monthly_payroll',
                    ],
                    'attendance_trends' => [
                        '*' => ['day', 'Present', 'Late'],
                    ],
                    'department_allocation' => [
                        '*' => ['name', 'count', 'percentage'],
                    ],
                ],
            ]);
    }

    public function test_hr_dashboard_returns_operational_payload(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->getJson('/api/v1/dashboard/hr');

        $response->assertStatus(200)
            ->assertJsonPath('data.stats.active_staff', 2)
            ->assertJsonPath('data.stats.pending_leaves', 1)
            ->assertJsonStructure([
                'data' => [
                    'stats' => [
                        'active_staff',
                        'new_joiners_month',
                        'birthdays_this_week',
                        'pending_leaves',
                    ],
                    'onboarding_queue' => [
                        '*' => ['id', 'name', 'step', 'percent'],
                    ],
                    'document_verification' => [
                        '*' => ['id', 'name', 'doc', 'status'],
                    ],
                ],
            ]);
    }
}
