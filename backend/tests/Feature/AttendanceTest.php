<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\AttendanceRegularization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Shift $shift;
    protected Employee $employee;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Attendance Acme Corp',
            'is_active' => true,
        ]);

        $this->shift = Shift::create([
            'company_id' => $this->company->id,
            'name' => 'Standard Shift',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'grace_period_minutes' => 15,
            'half_day_minutes' => 240,
            'full_day_minutes' => 480,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'John Clerk',
            'email' => 'john.c@acme.com',
            'password' => bcrypt('password'),
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'first_name' => 'John',
            'last_name' => 'Clerk',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
        ]);
    }

    /**
     * Test geofence blocking.
     */
    public function test_clock_in_geofence_perimeter_check(): void
    {
        $service = new \App\Services\AttendanceService(new \App\Services\AttendanceEngine());

        // 1. Clock in outside perimeter (e.g. London coordinates) should throw exception
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage("Clock-in rejected: Location is outside the office allowable perimeter.");

        $service->clockIn($this->employee->id, 51.5074, -0.1278, '192.168.1.1');
    }

    /**
     * Test successful clock in within geofence.
     */
    public function test_clock_in_successful_within_geofence(): void
    {
        $service = new \App\Services\AttendanceService(new \App\Services\AttendanceEngine());

        // Office location coordinates: 25.2048, 55.2708
        $log = $service->clockIn($this->employee->id, 25.2048, 55.2708, '127.0.0.1');

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $this->employee->id,
        ]);
    }

    /**
     * Test regularization approval workflow.
     */
    public function test_regularization_approval(): void
    {
        $service = new \App\Services\AttendanceService(new \App\Services\AttendanceEngine());

        // 1. Submit regularization
        $req = $service->requestRegularization(
            $this->employee->id,
            now()->subDay()->toDateString(),
            '09:00:00',
            '17:00:00',
            'Forgot to clock in'
        );

        $this->assertDatabaseHas('attendance_regularizations', [
            'id' => $req->id,
            'status' => 'Pending',
        ]);

        // 2. Approve regularization
        $service->approveRegularization($req->id, $this->user->id);

        $this->assertDatabaseHas('attendance_regularizations', [
            'id' => $req->id,
            'status' => 'Approved',
            'approved_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $this->employee->id,
            'is_regularized' => true,
            'status' => 'Present',
        ]);
    }
}
