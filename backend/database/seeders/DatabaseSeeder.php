<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            SubscriptionPlanSeeder::class,
            CompanySeeder::class,
            RolePermissionSeeder::class,
            OrgStructureSeeder::class,
            EmployeeSeeder::class,
            LeavePolicySeeder::class,
            LeaveBalanceSeeder::class,
            AnnouncementSeeder::class,
            AttendanceLogSeeder::class,
            AdminWorkflowSeeder::class,
        ]);
    }
}
