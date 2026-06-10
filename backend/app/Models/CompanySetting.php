<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanySetting extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'timezone',
        'currency',
        'financial_year_start',
        'financial_year_end',
        'settings_data',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'financial_year_start' => 'date',
        'financial_year_end' => 'date',
        'settings_data' => 'array',
    ];
}
