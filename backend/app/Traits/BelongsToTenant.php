<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use App\Services\TenantContext;

trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait.
     *
     * @return void
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (app()->has(TenantContext::class)) {
                $tenantContext = app(TenantContext::class);
                
                if (empty($model->company_id)) {
                    $model->company_id = $tenantContext->getCompanyId();
                }
                
                if (empty($model->branch_id) && \Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), 'branch_id')) {
                    $model->branch_id = $tenantContext->getBranchId();
                }
            }
        });
    }

    /**
     * Relationship to the Company owning this resource.
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }
}
