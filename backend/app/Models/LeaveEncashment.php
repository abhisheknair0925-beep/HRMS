<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveEncashment extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'employee_id',
        'leave_policy_id',
        'days_to_encash',
        'amount_per_day',
        'total_amount',
        'status',
        'approved_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'days_to_encash' => 'decimal:1',
        'amount_per_day' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the employee profile.
     *
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the policy encashed.
     *
     * @return BelongsTo
     */
    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id');
    }

    /**
     * Get the user who approved this encashment.
     *
     * @return BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
