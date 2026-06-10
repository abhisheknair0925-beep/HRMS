<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRegularization extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'attendance_log_id',
        'employee_id',
        'requested_date',
        'requested_clock_in',
        'requested_clock_out',
        'reason',
        'status',
        'approved_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'requested_clock_in' => 'datetime',
        'requested_clock_out' => 'datetime',
    ];

    /**
     * Get the employee profile requesting correction.
     *
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the associated attendance log.
     *
     * @return BelongsTo
     */
    public function attendanceLog(): BelongsTo
    {
        return $this->belongsTo(AttendanceLog::class, 'attendance_log_id');
    }

    /**
     * Get the user who approved this correction.
     *
     * @return BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
