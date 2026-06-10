<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgStructureTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $managerUser;
    protected User $reportUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Org Acme Corp',
            'is_active' => true,
        ]);

        $this->managerUser = User::create([
            'company_id' => $this->company->id,
            'name' => 'Jane Boss',
            'email' => 'jane.b@acme.com',
            'password' => bcrypt('password'),
        ]);

        $this->reportUser = User::create([
            'company_id' => $this->company->id,
            'name' => 'Tim Worker',
            'email' => 'tim.w@acme.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Test creating departments and designations scoped correctly.
     */
    public function test_departments_and_designations_creation(): void
    {
        $dept = Department::create([
            'company_id' => $this->company->id,
            'name' => 'Engineering',
            'description' => 'Tech Division',
            'manager_id' => $this->managerUser->id,
        ]);

        $desig = Designation::create([
            'company_id' => $this->company->id,
            'department_id' => $dept->id,
            'title' => 'Software Engineer',
            'salary_grade' => 'Grade 3',
        ]);

        $this->assertDatabaseHas('departments', ['name' => 'Engineering']);
        $this->assertDatabaseHas('designations', ['title' => 'Software Engineer']);
    }

    /**
     * Test transferring an employee.
     */
    public function test_employee_transfer(): void
    {
        $deptOld = Department::create(['company_id' => $this->company->id, 'name' => 'QA']);
        $deptNew = Department::create(['company_id' => $this->company->id, 'name' => 'DevOps']);

        $employee = Employee::create([
            'company_id' => $this->company->id,
            'user_id' => $this->reportUser->id,
            'first_name' => 'Tim',
            'last_name' => 'Worker',
            'joining_date' => now()->toDateString(),
            'department_id' => $deptOld->id,
        ]);

        // Trigger transfer via Service Layer
        $service = new \App\Services\OrgStructureService();
        $service->transferEmployee($employee->id, $deptNew->id, null, 'Promotion to DevOps team');

        $employee->refresh();
        $this->assertEquals($deptNew->id, $employee->department_id);

        // Assert Transfer history was created
        $this->assertDatabaseHas('employee_transfers', [
            'employee_id' => $employee->id,
            'old_department_id' => $deptOld->id,
            'new_department_id' => $deptNew->id,
            'reason' => 'Promotion to DevOps team',
        ]);
    }

    /**
     * Test recursive organization chart structure.
     */
    public function test_organization_tree_generation(): void
    {
        // 1. Manager employee
        $managerEmp = Employee::create([
            'company_id' => $this->company->id,
            'user_id' => $this->managerUser->id,
            'first_name' => 'Jane',
            'last_name' => 'Boss',
            'joining_date' => now()->toDateString(),
        ]);

        // 2. Report employee reporting to manager user_id
        $reportEmp = Employee::create([
            'company_id' => $this->company->id,
            'user_id' => $this->reportUser->id,
            'manager_id' => $this->managerUser->id,
            'first_name' => 'Tim',
            'last_name' => 'Worker',
            'joining_date' => now()->toDateString(),
        ]);

        $service = new \App\Services\OrgStructureService();
        $tree = $service->getOrganizationTree();

        $this->assertCount(1, $tree); // Manager is at top level
        $this->assertEquals('Jane Boss', $tree[0]['name']);
        $this->assertCount(1, $tree[0]['children']); // Tim is a child of Jane
        $this->assertEquals('Tim Worker', $tree[0]['children'][0]['name']);
    }
}
