<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'employee_id',
        'leave_policy_id',
        'start_date',
        'end_date',
        'half_day',
        'total_days',
        'reason',
        'status',
        'approved_by',
        'rejection_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'half_day' => 'boolean',
        'total_days' => 'decimal:1',
    ];

    /**
     * Get the employee profile requesting leave.
     *
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the leave policy requested.
     *
     * @return BelongsTo
     */
    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id');
    }

    /**
     * Get the user who processed this request.
     *
     * @return BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
