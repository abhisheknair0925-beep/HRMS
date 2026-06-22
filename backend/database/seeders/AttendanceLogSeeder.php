<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\AttendanceLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceLogSeeder extends Seeder
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
        
        // Let's seed logs for the last 14 days
        $startDate = Carbon::now()->subDays(14);
        $endDate = Carbon::now();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip future logs or today's logs if it's currently early, but let's seed today up to now or mark as present
            if ($date->isFuture()) {
                continue;
            }

            // Skip weekends (Saturday and Sunday)
            if ($date->isWeekend()) {
                continue;
            }

            $dateString = $date->toDateString();

            foreach ($employees as $employee) {
                // Randomize attendance type: 85% Present, 10% Late, 5% Absent
                $rand = rand(1, 100);

                if ($rand <= 5) {
                    // Absent
                    AttendanceLog::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'log_date' => $dateString,
                        ],
                        [
                            'company_id' => $company->id,
                            'status' => 'Absent',
                            'working_minutes' => 0,
                            'overtime_minutes' => 0,
                            'is_regularized' => false,
                        ]
                    );
                } else {
                    // Present or Late
                    $isLate = ($rand > 5 && $rand <= 15);
                    
                    // Standard shift is 09:00:00 to 17:00:00
                    if ($isLate) {
                        // Late: clock in between 09:20 and 10:00
                        $clockInTime = $date->copy()->setTime(9, rand(20, 59), rand(0, 59));
                        $status = 'Late';
                    } else {
                        // On time: clock in between 08:45 and 09:05
                        $clockInTime = $date->copy()->setTime(8, rand(45, 59), rand(0, 59));
                        if ($clockInTime->minute > 15 && $clockInTime->hour == 9) {
                            $status = 'Late';
                        } else {
                            $status = 'Present';
                        }
                    }

                    // Clock out between 17:00 and 17:30
                    $clockOutTime = $date->copy()->setTime(17, rand(0, 30), rand(0, 59));
                    
                    $workingMinutes = (int) $clockInTime->diffInMinutes($clockOutTime);
                    $overtimeMinutes = 0;
                    if ($workingMinutes > 480) {
                        $overtimeMinutes = $workingMinutes - 480;
                    }

                    AttendanceLog::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'log_date' => $dateString,
                        ],
                        [
                            'company_id' => $company->id,
                            'clock_in' => $clockInTime->toDateTimeString(),
                            'clock_out' => $clockOutTime->toDateTimeString(),
                            'clock_in_ip' => '192.168.1.' . rand(2, 254),
                            'clock_out_ip' => '192.168.1.' . rand(2, 254),
                            'clock_in_latitude' => 37.774929 + (rand(-100, 100) / 100000.0),
                            'clock_in_longitude' => -122.419416 + (rand(-100, 100) / 100000.0),
                            'clock_out_latitude' => 37.774929 + (rand(-100, 100) / 100000.0),
                            'clock_out_longitude' => -122.419416 + (rand(-100, 100) / 100000.0),
                            'status' => $status,
                            'working_minutes' => $workingMinutes,
                            'overtime_minutes' => $overtimeMinutes,
                            'is_regularized' => false,
                        ]
                    );
                }
            }
        }
    }
}
