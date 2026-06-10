<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTransfer extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'employee_id',
        'old_department_id',
        'new_department_id',
        'old_designation_id',
        'new_designation_id',
        'transfer_date',
        'reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    /**
     * Get the employee profile transferred.
     *
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the old department.
     *
     * @return BelongsTo
     */
    public function oldDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'old_department_id');
    }

    /**
     * Get the new department.
     *
     * @return BelongsTo
     */
    public function newDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'new_department_id');
    }

    /**
     * Get the old designation.
     *
     * @return BelongsTo
     */
    public function oldDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'old_designation_id');
    }

    /**
     * Get the new designation.
     *
     * @return BelongsTo
     */
    public function newDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'new_designation_id');
    }
}
