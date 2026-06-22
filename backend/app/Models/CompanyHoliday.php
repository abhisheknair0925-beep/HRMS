<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CompanyHoliday extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'holiday_date',
        'type',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_active' => 'boolean',
    ];
}
