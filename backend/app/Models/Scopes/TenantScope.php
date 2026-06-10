<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->has(TenantContext::class)) {
            // Bypass tenant scope for global Super Admins
            if (auth()->hasUser() && auth()->user()->hasRole('Super Admin')) {
                return;
            }

            $companyId = app(TenantContext::class)->getCompanyId();
            if ($companyId !== null) {
                $builder->where($model->getTable() . '.company_id', $companyId);
            }
        }
    }
}
