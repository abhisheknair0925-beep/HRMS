<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'subdomain',
        'domain',
        'logo_url',
        'is_active',
        'subscription_plan_id',
        'subscription_ends_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Get the branches associated with this company.
     *
     * @return HasMany
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'company_id');
    }

    /**
     * Get the settings associated with this company.
     *
     * @return HasOne
     */
    public function settings(): HasOne
    {
        return $this->hasOne(CompanySetting::class, 'company_id');
    }

    /**
     * Get the active subscription plan of the company.
     *
     * @return BelongsTo
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}
