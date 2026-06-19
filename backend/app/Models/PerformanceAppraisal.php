<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceAppraisal extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'employee_id',
        'reviewer_name',
        'review_date',
        'overall_score',
        'quality_score',
        'productivity_score',
        'teamwork_score',
        'communication_score',
        'comment',
    ];

    protected $casts = [
        'review_date' => 'date',
        'overall_score' => 'float',
        'quality_score' => 'integer',
        'productivity_score' => 'integer',
        'teamwork_score' => 'integer',
        'communication_score' => 'integer',
    ];

    /**
     * Get the employee profile this appraisal is for.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
