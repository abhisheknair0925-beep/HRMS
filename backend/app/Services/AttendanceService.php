<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\AttendanceRegularization;
use App\Exceptions\BusinessException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(protected AttendanceEngine $engine) {}

    /**
     * Clock in employee with geofence validation.
     *
     * @param string $employeeId
     * @param float $lat
     * @param float $lng
     * @param string $ip
     * @return AttendanceLog
     * @throws BusinessException
     */
    public function clockIn(string $employeeId, float $lat, float $lng, string $ip): AttendanceLog
    {
        $employee = Employee::with('shift')->find($employeeId);
        if (!$employee) {
            throw new BusinessException("Employee profile not found.", 404);
        }

        $shift = $employee->shift;
        if (!$shift) {
            throw new BusinessException("No working shift assigned to this employee.", 422);
        }

        // 1. Geofence Perimeter check
        if (!$this->engine->isWithinGeofence($lat, $lng)) {
            throw new BusinessException("Clock-in rejected: Location is outside the office allowable perimeter.", 422);
        }

        $today = Carbon::today()->toDateString();

        // 2. Check duplicate logs
        $existing = AttendanceLog::where('employee_id', $employeeId)
            ->where('log_date', $today)
            ->first();

        if ($existing) {
            throw new BusinessException("An attendance check-in entry already exists for today.", 422);
        }

        $now = Carbon::now();
        $metrics = $this->engine->calculateLogMetrics($now, null, $shift);

        return AttendanceLog::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employeeId,
            'log_date' => $today,
            'clock_in' => $now,
            'clock_in_ip' => $ip,
            'clock_in_latitude' => $lat,
            'clock_in_longitude' => $lng,
            'status' => $metrics['status'],
        ]);
    }

    /**
     * Clock out employee with geofence validation.
     *
     * @param string $employeeId
     * @param float $lat
     * @param float $lng
     * @param string $ip
     * @return AttendanceLog
     * @throws BusinessException
     */
    public function clockOut(string $employeeId, float $lat, float $lng, string $ip): AttendanceLog
    {
        $employee = Employee::with('shift')->find($employeeId);
        if (!$employee) {
            throw new BusinessException("Employee profile not found.", 404);
        }

        $shift = $employee->shift;
        if (!$shift) {
            throw new BusinessException("No working shift assigned to this employee.", 422);
        }

        // 1. Geofence Perimeter check
        if (!$this->engine->isWithinGeofence($lat, $lng)) {
            throw new BusinessException("Clock-out rejected: Location is outside the office allowable perimeter.", 422);
        }

        $today = Carbon::today()->toDateString();

        // 2. Find today's check-in entry
        $log = AttendanceLog::where('employee_id', $employeeId)
            ->where('log_date', $today)
            ->first();

        if (!$log || !$log->clock_in) {
            throw new BusinessException("No check-in record found for today. Please clock in first.", 422);
        }

        if ($log->clock_out) {
            throw new BusinessException("You have already checked out for today.", 422);
        }

        $now = Carbon::now();
        $metrics = $this->engine->calculateLogMetrics($log->clock_in, $now, $shift);

        $log->update([
            'clock_out' => $now,
            'clock_out_ip' => $ip,
            'clock_out_latitude' => $lat,
            'clock_out_longitude' => $lng,
            'status' => $metrics['status'],
            'working_minutes' => $metrics['working_minutes'],
            'overtime_minutes' => $metrics['overtime_minutes'],
        ]);

        return $log;
    }

    /**
     * Submit an attendance regularization request.
     *
     * @param string $employeeId
     * @param string $date
     * @param string|null $clockInTime
     * @param string|null $clockOutTime
     * @param string $reason
     * @return AttendanceRegularization
     */
    public function requestRegularization(
        string $employeeId,
        string $date,
        ?string $clockInTime,
        ?string $clockOutTime,
        string $reason
    ): AttendanceRegularization {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            throw new \RuntimeException("Employee not found.");
        }

        // Find existing log for the requested date if it exists
        $log = AttendanceLog::where('employee_id', $employeeId)
            ->where('log_date', $date)
            ->first();

        return AttendanceRegularization::create([
            'company_id' => $employee->company_id,
            'attendance_log_id' => $log?->id,
            'employee_id' => $employeeId,
            'requested_date' => $date,
            'requested_clock_in' => $clockInTime ? Carbon::parse($date . ' ' . $clockInTime) : null,
            'requested_clock_out' => $clockOutTime ? Carbon::parse($date . ' ' . $clockOutTime) : null,
            'reason' => $reason,
            'status' => 'Pending',
        ]);
    }

    /**
     * Approve and apply regularization request.
     *
     * @param string $requestId
     * @param string $approverId
     * @return AttendanceRegularization
     * @throws BusinessException
     */
    public function approveRegularization(string $requestId, string $approverId): AttendanceRegularization
    {
        return DB::transaction(function () use ($requestId, $approverId) {
            $request = AttendanceRegularization::lockForUpdate()->find($requestId);
            if (!$request) {
                throw new BusinessException("Regularization request not found.", 404);
            }

            if ($request->status !== 'Pending') {
                throw new BusinessException("This request has already been processed.", 422);
            }

            $employee = Employee::with('shift')->find($request->employee_id);
            if (!$employee) {
                throw new BusinessException("Associated employee profile no longer exists.", 404);
            }

            $shift = $employee->shift;
            if (!$shift) {
                throw new BusinessException("Employee does not have an active shift configuration.", 422);
            }

            // Find or create attendance log
            $log = AttendanceLog::where('employee_id', $request->employee_id)
                ->where('log_date', $request->requested_date->toDateString())
                ->first();

            if (!$log) {
                $log = new AttendanceLog();
                $log->company_id = $employee->company_id;
                $log->employee_id = $request->employee_id;
                $log->log_date = $request->requested_date;
            }

            // Update log metrics
            $log->clock_in = $request->requested_clock_in;
            $log->clock_out = $request->requested_clock_out;
            $log->is_regularized = true;

            $metrics = $this->engine->calculateLogMetrics(
                $request->requested_clock_in,
                $request->requested_clock_out,
                $shift
            );

            $log->status = $metrics['status'];
            $log->working_minutes = $metrics['working_minutes'];
            $log->overtime_minutes = $metrics['overtime_minutes'];
            $log->save();

            // Approve request
            $request->update([
                'attendance_log_id' => $log->id,
                'status' => 'Approved',
                'approved_by' => $approverId,
            ]);

            return $request;
        });
    }
}
