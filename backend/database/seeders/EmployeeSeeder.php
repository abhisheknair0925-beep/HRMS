<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Resolve Company and Branch
        $company = Company::where('subdomain', 'humanode')->first();
        $branch = Branch::where('company_id', $company->id)->where('code', 'HQ')->first();
        $shift = Shift::where('company_id', $company->id)->where('name', 'General Shift')->first();

        if (!$company || !$branch) {
            return;
        }

        // Resolve Departments
        $deptExec = Department::where('company_id', $company->id)->where('name', 'Executive Operations')->first();
        $deptHR = Department::where('company_id', $company->id)->where('name', 'Human Resources')->first();
        $deptProd = Department::where('company_id', $company->id)->where('name', 'Product Management')->first();
        $deptEng = Department::where('company_id', $company->id)->where('name', 'Software Engineering')->first();

        // Resolve Designations
        $desigCeo = Designation::where('company_id', $company->id)->where('title', 'CEO')->first();
        $desigHrDir = Designation::where('company_id', $company->id)->where('title', 'HR Director')->first();
        $desigSpm = Designation::where('company_id', $company->id)->where('title', 'Senior Product Manager')->first();
        $desigFed = Designation::where('company_id', $company->id)->where('title', 'Frontend Developer')->first();

        // Define personas
        $personas = [
            'admin' => [
                'name' => 'Admin User',
                'email' => 'admin@humanode.net',
                'password' => 'Welcome@HumaNode123',
                'roles' => ['Admin', 'Super Admin', 'Company Admin'],
                'emp' => [
                    'employee_id' => 'EMP-2026-0001',
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'phone' => '+15550101',
                    'gender' => 'Male',
                    'marital_status' => 'Married',
                    'status' => 'Active',
                    'department_id' => $deptExec?->id,
                    'designation_id' => $desigCeo?->id,
                    'joining_date' => '2026-01-01',
                ]
            ],
            'hr' => [
                'name' => 'Sarah HR',
                'email' => 'hr@humanode.net',
                'password' => 'Welcome@HumaNode123',
                'roles' => ['HR'],
                'emp' => [
                    'employee_id' => 'EMP-2026-0002',
                    'first_name' => 'Sarah',
                    'last_name' => 'HR',
                    'phone' => '+15550102',
                    'gender' => 'Female',
                    'marital_status' => 'Single',
                    'status' => 'Active',
                    'department_id' => $deptHR?->id,
                    'designation_id' => $desigHrDir?->id,
                    'joining_date' => '2026-01-15',
                ]
            ],
            'manager' => [
                'name' => 'Sarah Manager',
                'email' => 'manager@humanode.net',
                'password' => 'Welcome@HumaNode123',
                'roles' => ['Manager'],
                'emp' => [
                    'employee_id' => 'EMP-2026-0003',
                    'first_name' => 'Sarah',
                    'last_name' => 'Manager',
                    'phone' => '+15550103',
                    'gender' => 'Female',
                    'marital_status' => 'Married',
                    'status' => 'Active',
                    'department_id' => $deptProd?->id,
                    'designation_id' => $desigSpm?->id,
                    'joining_date' => '2026-02-01',
                ]
            ],
            'employee' => [
                'name' => 'John Employee',
                'email' => 'employee@humanode.net',
                'password' => 'Welcome@HumaNode123',
                'roles' => ['Employee'],
                'emp' => [
                    'employee_id' => 'EMP-2026-0004',
                    'first_name' => 'John',
                    'last_name' => 'Employee',
                    'phone' => '+15550104',
                    'gender' => 'Male',
                    'marital_status' => 'Single',
                    'status' => 'Active',
                    'department_id' => $deptEng?->id,
                    'designation_id' => $desigFed?->id,
                    'joining_date' => '2026-03-01',
                ]
            ]
        ];

        $users = [];
        $employees = [];

        // 1. Create User and Employee objects (without managers first)
        foreach ($personas as $key => $p) {
            $user = User::updateOrCreate(
                ['email' => $p['email']],
                [
                    'company_id' => $company->id,
                    'name' => $p['name'],
                    'password' => Hash::make($p['password']),
                    'is_active' => true,
                ]
            );

            // Sync roles
            $user->syncRoles($p['roles']);

            $employee = Employee::updateOrCreate(
                ['company_id' => $company->id, 'employee_id' => $p['emp']['employee_id']],
                array_merge($p['emp'], [
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'shift_id' => $shift?->id,
                    'personal_info' => [
                        'blood_group' => 'O+',
                        'nationality' => 'American',
                    ],
                    'bank_details' => [
                        'bank_name' => 'Silicon Valley Bank',
                        'account_number' => '1234567890',
                        'routing_number' => '987654321',
                    ]
                ])
            );

            $users[$key] = $user;
            $employees[$key] = $employee;
        }

        // 2. Set manager linkages (manager_id references users.id)
        $employees['hr']->update(['manager_id' => $users['admin']->id]);
        $employees['manager']->update(['manager_id' => $users['admin']->id]);
        $employees['employee']->update(['manager_id' => $users['manager']->id]);

        // 3. Update department managers
        $deptExec?->update(['manager_id' => $users['admin']->id]);
        $deptHR?->update(['manager_id' => $users['hr']->id]);
        $deptProd?->update(['manager_id' => $users['manager']->id]);
        $deptEng?->update(['manager_id' => $users['manager']->id]);
    }
}
