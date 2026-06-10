<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveEncashment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Employee $employee;
    protected User $user;
    protected LeavePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Leave Acme Corp',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Sarah HR',
            'email' => 'sarah.hr@acme.com',
            'password' => bcrypt('password'),
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'first_name' => 'Sarah',
            'last_name' => 'HR',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
        ]);

        $this->policy = LeavePolicy::create([
            'company_id' => $this->company->id,
            'name' => 'Annual Leave',
            'total_days' => 15.0,
        ]);
    }

    /**
     * Test balance checks.
     */
    public function test_insufficient_leave_balance_fails(): void
    {
        // Allocate only 2 days
        LeaveBalance::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'leave_policy_id' => $this->policy->id,
            'allocated_days' => 2.0,
            'used_days' => 0.0,
        ]);

        $service = new \App\Services\LeaveService();

        // Attempting to apply for 5 days (e.g. today to 4 days later) should fail
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage("Insufficient leave balance.");

        $service->applyForLeave(
            $this->employee->id,
            $this->policy->id,
            now()->toDateString(),
            now()->addDays(4)->toDateString(),
            false,
            'Vacation'
        );
    }

    /**
     * Test successful leave request and approval flow.
     */
    public function test_leave_apply_and_approval_flow(): void
    {
        $balance = LeaveBalance::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'leave_policy_id' => $this->policy->id,
            'allocated_days' => 15.0,
            'used_days' => 0.0,
        ]);

        $service = new \App\Services\LeaveService();

        // Apply for 3 days
        $req = $service->applyForLeave(
            $this->employee->id,
            $this->policy->id,
            now()->toDateString(),
            now()->addDays(2)->toDateString(),
            false,
            'Vacation'
        );

        $this->assertDatabaseHas('leave_requests', [
            'id' => $req->id,
            'status' => 'Pending',
            'total_days' => 3.0,
        ]);

        // Approve request
        $service->approveLeave($req->id, $this->user->id);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $req->id,
            'status' => 'Approved',
        ]);

        $balance->refresh();
        $this->assertEquals(3.0, $balance->used_days);
    }

    /**
     * Test leave encashment workflow.
     */
    public function test_leave_encashment_workflow(): void
    {
        $balance = LeaveBalance::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'leave_policy_id' => $this->policy->id,
            'allocated_days' => 10.0,
            'used_days' => 0.0,
            'encashed_days' => 0.0,
        ]);

        $service = new \App\Services\LeaveService();

        // 1. Request encashment for 5 days
        $encash = $service->requestEncashment($this->employee->id, $this->policy->id, 5.0, 100.0);

        $this->assertDatabaseHas('leave_encashments', [
            'id' => $encash->id,
            'status' => 'Pending',
            'total_amount' => 500.0,
        ]);

        // 2. Approve encashment
        $service->approveEncashment($encash->id, $this->user->id);

        $this->assertDatabaseHas('leave_encashments', [
            'id' => $encash->id,
            'status' => 'Approved',
        ]);

        $balance->refresh();
        $this->assertEquals(5.0, $balance->encashed_days);
    }
}
