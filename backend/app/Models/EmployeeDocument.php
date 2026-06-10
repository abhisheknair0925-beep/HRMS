<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'employee_id',
        'name',
        'type',
        'file_url',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the employee profile associated with this document.
     *
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
