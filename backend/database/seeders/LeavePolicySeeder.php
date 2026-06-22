<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\LeavePolicy;
use Illuminate\Database\Seeder;

class LeavePolicySeeder extends Seeder
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

        $policies = [
            [
                'name' => 'Annual Leave',
                'total_days' => 15.0,
                'carry_over_max' => 5.0,
                'accrual_rate' => 'monthly',
            ],
            [
                'name' => 'Casual Leave',
                'total_days' => 10.0,
                'carry_over_max' => 0.0,
                'accrual_rate' => 'yearly',
            ],
            [
                'name' => 'Medical Leave',
                'total_days' => 7.0,
                'carry_over_max' => 0.0,
                'accrual_rate' => 'yearly',
            ],
            [
                'name' => 'Unpaid Leave',
                'total_days' => 99.0,
                'carry_over_max' => 0.0,
                'accrual_rate' => 'none',
            ],
        ];

        foreach ($policies as $policy) {
            LeavePolicy::updateOrCreate(
                ['company_id' => $company->id, 'name' => $policy['name']],
                $policy
            );
        }
    }
}
