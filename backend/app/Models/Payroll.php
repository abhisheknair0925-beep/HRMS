<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'employee_id',
        'base_pay',
        'hra',
        'allowance',
        'pf',
        'tax',
        'revisions',
    ];

    protected $casts = [
        'base_pay' => 'float',
        'hra' => 'float',
        'allowance' => 'float',
        'pf' => 'float',
        'tax' => 'float',
        'revisions' => 'array',
    ];

    /**
     * Get the employee profile linked to this payroll structure.
     *
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
