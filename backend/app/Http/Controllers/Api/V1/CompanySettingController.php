<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Services\TenantContext;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanySettingController extends Controller
{
    use ApiResponseTrait;

    /**
     * Retrieve the company settings for the resolved tenant.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $tenantContext = app(TenantContext::class);
        $companyId = $tenantContext->getCompanyId();

        if (!$companyId) {
            return $this->errorResponse('Tenant company context not resolved.', 400);
        }

        $settings = CompanySetting::where('company_id', $companyId)->first();

        if (!$settings) {
            // Lazy load settings if they don't exist
            $settings = CompanySetting::create([
                'company_id' => $companyId,
                'timezone' => 'UTC',
                'currency' => 'USD',
            ]);
        }

        return $this->successResponse($settings, 'Company settings retrieved.');
    }

    /**
     * Update the company settings.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $tenantContext = app(TenantContext::class);
        $companyId = $tenantContext->getCompanyId();

        if (!$companyId) {
            return $this->errorResponse('Tenant company context not resolved.', 400);
        }

        $settings = CompanySetting::where('company_id', $companyId)->first();

        if (!$settings) {
            $settings = new CompanySetting();
            $settings->company_id = $companyId;
        }

        $validated = $request->validate([
            'timezone' => 'sometimes|required|string',
            'currency' => 'sometimes|required|string|size:3',
            'financial_year_start' => 'sometimes|required|date',
            'financial_year_end' => 'sometimes|required|date|after:financial_year_start',
            'settings_data' => 'sometimes|nullable|array',
        ]);

        $settings->fill($validated);
        $settings->save();

        return $this->successResponse($settings, 'Company settings updated successfully.');
    }
}
