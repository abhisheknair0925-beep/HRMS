<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use App\Models\Company;
use App\Models\Branch;
use App\Services\TenantContext;
use App\Exceptions\TenantException;
use Illuminate\Http\Request;

class TenantMiddleware
{
    /**
     * Intercept the incoming request, resolve the tenant company and branch, and configure the TenantContext.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws TenantException
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Resolve Company ID from header or authenticated user
        $companyId = $request->header('X-Company-ID') ?? ($request->user() ? $request->user()->company_id : null);

        if (!$companyId) {
            throw new TenantException("Missing multi-tenant context boundary. Please provide X-Company-ID header.", 400);
        }

        // 2. Validate Company existence
        $company = Company::find($companyId);
        if (!$company) {
            throw new TenantException("Tenant company not registered or has been suspended.", 404);
        }

        if (!$company->is_active) {
            throw new TenantException("Tenant company account is currently deactivated.", 403);
        }

        // Configure Company Context
        $tenantContext = app(TenantContext::class);
        $tenantContext->setCompany($company);

        // 3. Resolve optional Branch ID from header
        $branchId = $request->header('X-Branch-ID');
        if ($branchId) {
            $branch = Branch::where('id', $branchId)
                ->where('company_id', $companyId)
                ->first();

            if (!$branch) {
                throw new TenantException("The requested branch does not exist or does not belong to your company.", 404);
            }

            if (!$branch->is_active) {
                throw new TenantException("The requested branch is currently deactivated.", 403);
            }

            $tenantContext->setBranch($branch);
        }

        return $next($request);
    }
}
