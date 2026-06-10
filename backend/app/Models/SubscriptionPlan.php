<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'price',
        'billing_cycle',
        'employee_limit',
        'features',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'employee_limit' => 'integer',
        'features' => 'array',
    ];

    /**
     * Get the companies subscribed to this plan.
     *
     * @return HasMany
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'subscription_plan_id');
    }
}
