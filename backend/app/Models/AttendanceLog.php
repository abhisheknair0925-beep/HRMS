<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceLog extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'employee_id',
        'log_date',
        'clock_in',
        'clock_out',
        'clock_in_ip',
        'clock_out_ip',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'status',
        'working_minutes',
        'overtime_minutes',
        'is_regularized',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'log_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'is_regularized' => 'boolean',
        'working_minutes' => 'integer',
        'overtime_minutes' => 'integer',
    ];

    /**
     * Get the employee profile owning this attendance log.
     *
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get regularization requests matching this log.
     *
     * @return HasMany
     */
    public function regularizations(): HasMany
    {
        return $this->hasMany(AttendanceRegularization::class, 'attendance_log_id');
    }
}
