<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingCandidate extends Model
{
    use HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'role',
        'department',
        'joining_date',
        'status',
        'emp_id',
        'docs_verified',
        'induction_scheduled',
        'induction_details',
        'assets',
        'checklist',
    ];

    protected $casts = [
        'docs_verified' => 'boolean',
        'induction_scheduled' => 'boolean',
        'induction_details' => 'array',
        'assets' => 'array',
        'checklist' => 'array',
        'joining_date' => 'date',
    ];
}
