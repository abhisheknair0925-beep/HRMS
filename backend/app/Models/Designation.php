<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    use HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'department_id',
        'title',
        'salary_grade',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the department associated with this designation.
     *
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get employees having this designation.
     *
     * @return HasMany
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'designation_id');
    }
}
