<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use Illuminate\Database\Seeder;

class LeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $company = Company::where('subdomain', 'humanode')->first();
        if (!$company) {
            return;
        }

        $employees = Employee::where('company_id', $company->id)->get();
        $policies = LeavePolicy::where('company_id', $company->id)->get();

        foreach ($employees as $employee) {
            foreach ($policies as $policy) {
                // Determine some randomized or standard usage
                $used = 0.0;
                if ($policy->name === 'Annual Leave') {
                    $used = 2.0;
                } elseif ($policy->name === 'Casual Leave') {
                    $used = 1.0;
                }

                LeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_policy_id' => $policy->id,
                    ],
                    [
                        'company_id' => $company->id,
                        'allocated_days' => $policy->total_days,
                        'used_days' => $used,
                        'encashed_days' => 0.0,
                    ]
                );
            }
        }
    }
}
