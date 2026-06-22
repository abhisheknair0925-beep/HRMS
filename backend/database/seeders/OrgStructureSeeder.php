<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class OrgStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Get seeded company
        $company = Company::where('subdomain', 'humanode')->first();
        if (!$company) {
            return;
        }

        // 1. Seed Branch
        $branch = Branch::updateOrCreate(
            ['company_id' => $company->id, 'code' => 'HQ'],
            [
                'name' => 'HQ Office',
                'is_active' => true,
            ]
        );

        // 2. Seed Shifts
        $shift = Shift::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'General Shift'],
            [
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'grace_period_minutes' => 15,
                'half_day_minutes' => 240,
                'full_day_minutes' => 480,
            ]
        );

        // 3. Seed Departments
        $depts = [
            ['name' => 'Executive Operations', 'description' => 'C-Suite and governance'],
            ['name' => 'Product Management', 'description' => 'Product strategy and planning'],
            ['name' => 'Human Resources', 'description' => 'Talent acquisition, policies, and benefits'],
            ['name' => 'Software Engineering', 'description' => 'Development and product engineering'],
        ];

        $seededDepts = [];
        foreach ($depts as $dept) {
            $seededDepts[$dept['name']] = Department::updateOrCreate(
                ['company_id' => $company->id, 'name' => $dept['name']],
                [
                    'description' => $dept['description'],
                ]
            );
        }

        // 4. Seed Designations
        $designations = [
            ['title' => 'CEO', 'salary_grade' => 'Grade L1', 'dept' => 'Executive Operations'],
            ['title' => 'Senior Product Manager', 'salary_grade' => 'Grade L3', 'dept' => 'Product Management'],
            ['title' => 'HR Director', 'salary_grade' => 'Grade L3', 'dept' => 'Human Resources'],
            ['title' => 'Frontend Developer', 'salary_grade' => 'Grade L4', 'dept' => 'Software Engineering'],
            ['title' => 'Backend Engineer', 'salary_grade' => 'Grade L4', 'dept' => 'Software Engineering'],
        ];

        foreach ($designations as $desig) {
            $deptModel = $seededDepts[$desig['dept']] ?? null;
            Designation::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'department_id' => $deptModel?->id,
                    'title' => $desig['title']
                ],
                [
                    'salary_grade' => $desig['salary_grade']
                ]
            );
        }
    }
}
