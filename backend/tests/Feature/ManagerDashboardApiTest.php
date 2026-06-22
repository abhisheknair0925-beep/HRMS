<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ManagerDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $manager;
    private Employee $directReport;
    private LeaveRequest $directReportLeave;
    private Shift $generalShift;
    private Shift $nightShift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Acme Manager Corp',
            'is_active' => true,
        ]);

        $this->manager = User::create([
            'company_id' => $this->company->id,
            'name' => 'Mary Manager',
            'email' => 'manager.portal@acme.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->generalShift = Shift::create([
            'company_id' => $this->company->id,
            'name' => 'General Shift',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'grace_period_minutes' => 15,
            'half_day_minutes' => 240,
            'full_day_minutes' => 480,
        ]);

        $this->nightShift = Shift::create([
            'company_id' => $this->company->id,
            'name' => 'Night Shift',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'grace_period_minutes' => 15,
            'half_day_minutes' => 240,
            'full_day_minutes' => 480,
        ]);

        $this->directReport = Employee::create([
            'company_id' => $this->company->id,
            'manager_id' => $this->manager->id,
            'shift_id' => $this->generalShift->id,
            'first_name' => 'John',
            'last_name' => 'Employee',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
        ]);

        AttendanceLog::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->directReport->id,
            'log_date' => now()->toDateString(),
            'clock_in' => now()->startOfDay()->addHours(9)->toDateTimeString(),
            'status' => 'Present',
            'working_minutes' => 480,
        ]);

        $leavePolicy = LeavePolicy::create([
            'company_id' => $this->company->id,
            'name' => 'Casual Leave',
            'total_days' => 10,
        ]);

        LeaveBalance::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->directReport->id,
            'leave_policy_id' => $leavePolicy->id,
            'allocated_days' => 10,
            'used_days' => 0,
            'encashed_days' => 0,
        ]);

        $this->directReportLeave = LeaveRequest::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->directReport->id,
            'leave_policy_id' => $leavePolicy->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'total_days' => 1,
            'reason' => 'Personal work',
            'status' => 'Pending',
        ]);

        $otherEmployee = Employee::create([
            'company_id' => $this->company->id,
            'first_name' => 'Outside',
            'last_name' => 'Scope',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
        ]);

        LeaveRequest::create([
            'company_id' => $this->company->id,
            'employee_id' => $otherEmployee->id,
            'leave_policy_id' => $leavePolicy->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'total_days' => 1,
            'reason' => 'Should not be visible',
            'status' => 'Pending',
        ]);
    }

    public function test_manager_dashboard_returns_direct_reports_and_pending_team_leaves(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->getJson('/api/v1/manager/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.checked_in_count', 1)
            ->assertJsonPath('data.summary.total_team_count', 1)
            ->assertJsonPath('data.summary.pending_leave_requests', 1)
            ->assertJsonFragment(['name' => 'John Employee'])
            ->assertJsonMissing(['employee_name' => 'Outside Scope'])
            ->assertJsonStructure([
                'data' => [
                    'team' => [
                        '*' => ['id', 'name', 'role', 'shift_id', 'shift', 'todayStatus', 'clockInTime', 'timesheet'],
                    ],
                    'leave_requests' => [
                        '*' => ['id', 'employee_name', 'policy_name', 'start_date', 'end_date', 'days', 'reason', 'status'],
                    ],
                    'shifts' => [
                        '*' => ['id', 'name', 'start_time', 'end_time'],
                    ],
                    'summary',
                ],
            ]);
    }

    public function test_manager_can_approve_direct_report_leave(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->postJson("/api/v1/manager/leaves/{$this->directReportLeave->id}/approve");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'Approved');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $this->directReportLeave->id,
            'status' => 'Approved',
            'approved_by' => $this->manager->id,
        ]);
    }

    public function test_manager_can_reject_direct_report_leave(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->postJson("/api/v1/manager/leaves/{$this->directReportLeave->id}/reject", [
                'rejection_reason' => 'Coverage needed.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'Rejected');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $this->directReportLeave->id,
            'status' => 'Rejected',
            'approved_by' => $this->manager->id,
            'rejection_reason' => 'Coverage needed.',
        ]);
    }

    public function test_manager_can_update_direct_report_shift(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->putJson("/api/v1/manager/direct-reports/{$this->directReport->id}/shift", [
                'shift_id' => $this->nightShift->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.shift', 'Night Shift');

        $this->assertDatabaseHas('employees', [
            'id' => $this->directReport->id,
            'shift_id' => $this->nightShift->id,
        ]);
    }
}
