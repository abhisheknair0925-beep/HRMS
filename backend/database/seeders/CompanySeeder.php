<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Fetch standard pro plan
        $proPlan = SubscriptionPlan::where('code', 'pro')->first();

        // Create main company
        $company = Company::updateOrCreate(
            ['subdomain' => 'humanode'],
            [
                'name' => 'HumaNode Technologies',
                'domain' => 'humanode.net',
                'is_active' => true,
                'subscription_plan_id' => $proPlan?->id,
                'subscription_ends_at' => now()->addYear(),
            ]
        );

        // Create company setting
        // Since CompanySetting uses BelongsToTenant, we must set company_id directly since TenantContext isn't available
        CompanySetting::updateOrCreate(
            ['company_id' => $company->id],
            [
                'timezone' => 'UTC',
                'currency' => 'USD',
                'financial_year_start' => now()->startOfYear()->toDateString(),
                'financial_year_end' => now()->endOfYear()->toDateString(),
                'settings_data' => [
                    'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                    'allow_field_checkin' => true,
                ],
            ]
        );
    }
}
