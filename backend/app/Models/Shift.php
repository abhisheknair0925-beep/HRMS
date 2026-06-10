<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'half_day_minutes',
        'full_day_minutes',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the employees assigned to this shift.
     *
     * @return HasMany
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'shift_id');
    }
}
