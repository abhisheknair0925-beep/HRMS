<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
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

        $announcements = [
            [
                'title' => 'Welcome to HumaNode HRMS!',
                'content' => 'We are thrilled to launch the new corporate HRMS portal. Here you can track attendance, apply for leaves, download payslips, and check your profile.',
                'is_active' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Quarterly Townhall Meeting',
                'content' => 'Our Q2 townhall meeting is scheduled for next Friday at 10:00 AM UTC. Please join the online video conference link sent to your corporate email calendar.',
                'is_active' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'System Maintenance Window',
                'content' => 'The API servers will undergo a routine security patch deployment this Saturday from 02:00 AM to 04:00 AM UTC. Minor interruptions may occur.',
                'is_active' => true,
                'published_at' => now(),
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::create(
                array_merge($announcement, [
                    'company_id' => $company->id,
                ])
            );
        }
    }
}
