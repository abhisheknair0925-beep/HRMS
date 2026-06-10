<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Shift;
use Carbon\Carbon;

class AttendanceEngine
{
    /**
     * Verify if coordinates fit within the allowable perimeter radius.
     *
     * @param float $lat
     * @param float $lng
     * @param float $officeLat
     * @param float $officeLng
     * @param float $radiusInMeters
     * @return bool
     */
    public function isWithinGeofence(
        float $lat,
        float $lng,
        float $officeLat = 25.2048,
        float $officeLng = 55.2708,
        float $radiusInMeters = 100.0
    ): bool {
        $earthRadius = 6371000; // Meters
        
        $dLat = deg2rad($officeLat - $lat);
        $dLng = deg2rad($officeLng - $lng);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat)) * cos(deg2rad($officeLat)) *
            sin($dLng / 2) * sin($dLng / 2);
            
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance <= $radiusInMeters;
    }

    /**
     * Compute working status, working minutes, and overtime minutes.
     *
     * @param Carbon $clockIn
     * @param Carbon|null $clockOut
     * @param Shift $shift
     * @return array
     */
    public function calculateLogMetrics(Carbon $clockIn, ?Carbon $clockOut, Shift $shift): array
    {
        $metrics = [
            'status' => 'Present',
            'working_minutes' => 0,
            'overtime_minutes' => 0,
        ];

        // 1. Check Late arrival status
        $shiftStart = Carbon::parse($clockIn->toDateString() . ' ' . $shift->start_time);
        $graceLimit = $shiftStart->copy()->addMinutes($shift->grace_period_minutes);

        if ($clockIn->isAfter($graceLimit)) {
            $metrics['status'] = 'Late';
        }

        // 2. Clock out minutes computations
        if ($clockOut !== null) {
            $totalWorking = (int) $clockIn->diffInMinutes($clockOut);
            metrics_working:
            $metrics['working_minutes'] = $totalWorking;

            // Resolve Half-day / Absent rules
            if ($totalWorking < $shift->half_day_minutes) {
                $metrics['status'] = 'Absent';
            } elseif ($totalWorking >= $shift->half_day_minutes && $totalWorking < $shift->full_day_minutes) {
                $metrics['status'] = 'Half-Day';
            }

            // Resolve Overtime (if working minutes exceed standard full day minutes)
            if ($totalWorking > $shift->full_day_minutes) {
                $metrics['overtime_minutes'] = $totalWorking - $shift->full_day_minutes;
            }
        }

        return $metrics;
    }
}
