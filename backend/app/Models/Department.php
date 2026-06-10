<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'manager_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the user who manages this department.
     *
     * @return BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the designations under this department.
     *
     * @return HasMany
     */
    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class, 'department_id');
    }

    /**
     * Get the employees inside this department.
     *
     * @return HasMany
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
