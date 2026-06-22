<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyHoliday;
use App\Models\CompOffRequest;
use App\Models\Employee;
use App\Models\EmployeeMessage;
use App\Models\PerformanceReview;
use Illuminate\Database\Seeder;

class AdminWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('subdomain', 'humanode')->first();
        if (!$company) {
            return;
        }

        $employee = Employee::where('company_id', $company->id)
            ->where('employee_id', 'EMP-2026-0004')
            ->first();

        $manager = Employee::where('company_id', $company->id)
            ->where('employee_id', 'EMP-2026-0003')
            ->first();

        foreach ([
            ['name' => 'New Year Day', 'holiday_date' => '2026-01-01', 'type' => 'Public Holiday'],
            ['name' => 'Labor Day', 'holiday_date' => '2026-05-01', 'type' => 'Public Holiday'],
            ['name' => 'National Day', 'holiday_date' => '2026-12-02', 'type' => 'Corporate Closed'],
            ['name' => 'HumaNode Hackathon Week', 'holiday_date' => '2026-06-22', 'type' => 'Event'],
        ] as $holiday) {
            CompanyHoliday::updateOrCreate(
                ['company_id' => $company->id, 'holiday_date' => $holiday['holiday_date'], 'name' => $holiday['name']],
                ['type' => $holiday['type'], 'is_active' => true]
            );
        }

        if ($employee) {
            CompOffRequest::updateOrCreate(
                ['company_id' => $company->id, 'employee_id' => $employee->id, 'worked_date' => '2026-06-13'],
                ['reason' => 'Weekend production server hotfix deployment.', 'status' => 'Pending']
            );

            PerformanceReview::updateOrCreate(
                ['company_id' => $company->id, 'employee_id' => $employee->id, 'review_date' => '2026-06-01'],
                [
                    'reviewer_id' => $manager?->user_id,
                    'overall_score' => 4.5,
                    'metrics' => [
                        ['name' => 'Quality', 'score' => 4.8],
                        ['name' => 'Productivity', 'score' => 4.5],
                        ['name' => 'Teamwork', 'score' => 4.2],
                        ['name' => 'Communication', 'score' => 4.5],
                    ],
                    'comment' => 'Reliable delivery and strong ownership across frontend work.',
                ]
            );

            EmployeeMessage::updateOrCreate(
                ['company_id' => $company->id, 'employee_id' => $employee->id, 'message' => 'Welcome to the HRMS communication thread.'],
                [
                    'sender_user_id' => $manager?->user_id,
                    'sender_type' => 'admin',
                    'sent_at' => now()->subHour(),
                ]
            );
        }
    }
}
