<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Tier',
                'code' => 'free',
                'price' => 0.00,
                'billing_cycle' => 'monthly',
                'employee_limit' => 10,
                'features' => ['ess_portal', 'attendance_tracking'],
            ],
            [
                'name' => 'Professional Plan',
                'code' => 'pro',
                'price' => 49.00,
                'billing_cycle' => 'monthly',
                'employee_limit' => 100,
                'features' => ['ess_portal', 'attendance_tracking', 'leave_management', 'document_locker'],
            ],
            [
                'name' => 'Enterprise Suite',
                'code' => 'enterprise',
                'price' => 199.00,
                'billing_cycle' => 'monthly',
                'employee_limit' => 9999,
                'features' => ['ess_portal', 'attendance_tracking', 'leave_management', 'document_locker', 'payroll_integration', 'advanced_analytics'],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['code' => $plan['code']],
                $plan
            );
        }
    }
}
