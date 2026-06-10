<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeavePolicy extends Model
{
    use HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'total_days',
        'carry_over_max',
        'accrual_rate',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_days' => 'decimal:1',
        'carry_over_max' => 'decimal:1',
    ];

    /**
     * Get the balances under this policy.
     *
     * @return HasMany
     */
    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'leave_policy_id');
    }

    /**
     * Get the requests submitted under this policy.
     *
     * @return HasMany
     */
    public function requests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'leave_policy_id');
    }
}
