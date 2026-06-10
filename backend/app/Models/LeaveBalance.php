<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'employee_id',
        'leave_policy_id',
        'allocated_days',
        'used_days',
        'encashed_days',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allocated_days' => 'decimal:1',
        'used_days' => 'decimal:1',
        'encashed_days' => 'decimal:1',
    ];

    /**
     * Get the employee profile owning this balance.
     *
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the leave policy mapped.
     *
     * @return BelongsTo
     */
    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id');
    }
}
