<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeMasterTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Acme Corporation',
            'is_active' => true,
        ]);
    }

    /**
     * Test that creating employees auto-generates sequential IDs.
     */
    public function test_employee_id_auto_generation(): void
    {
        // 1. Create first employee
        $emp1 = Employee::create([
            'company_id' => $this->company->id,
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
        ]);

        // 2. Create second employee
        $emp2 = Employee::create([
            'company_id' => $this->company->id,
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
        ]);

        $year = now()->format('Y');

        $this->assertEquals("EMP-{$year}-0001", $emp1->employee_id);
        $this->assertEquals("EMP-{$year}-0002", $emp2->employee_id);
    }

    /**
     * Test that JSON details persist correctly in the database.
     */
    public function test_employee_json_fields_persistence(): void
    {
        $employee = Employee::create([
            'company_id' => $this->company->id,
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
            'bank_details' => [
                'bank_name' => 'First National Bank',
                'account_number' => '1234567890',
            ],
            'emergency_contacts' => [
                [
                    'name' => 'Sally Brown',
                    'relationship' => 'Sister',
                    'phone' => '555-1234',
                ]
            ],
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Charlie',
        ]);

        $loaded = Employee::find($employee->id);
        $this->assertEquals('First National Bank', $loaded->bank_details['bank_name']);
        $this->assertEquals('Sister', $loaded->emergency_contacts[0]['relationship']);
    }
}
