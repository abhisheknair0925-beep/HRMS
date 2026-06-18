<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, HasUuids, Notifiable, SoftDeletes, LogsActivity;
    
    // Check if the user is a global Super Admin (which doesn't belong to a company)
    // If not, they must belong to a company and should be scoped by company_id.
    // So we can conditionalize BelongsToTenant or just boot it.
    // Wait, let's look at how we boot BelongsToTenant. In BelongsToTenant boot, we add TenantScope.
    // If we want global Super Admins to access all rows, we can bypass the scope in TenantScope.
    // Let's look at TenantScope:
    // If the authenticated user has a Super Admin role, we can bypass TenantScope!
    // That is a perfect SaaS implementation! Let's review TenantScope.
    // Yes! Let's implement that bypass in TenantScope or keep it simple.
    // If we want User model to also implement BelongsToTenant:
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Define the configuration for Spatie Activity Log.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active', 'company_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relation to the Employee profile.
     *
     * @return HasOne
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }
}
