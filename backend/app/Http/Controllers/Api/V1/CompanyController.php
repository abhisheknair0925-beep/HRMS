<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Services\TenantContext;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    use ApiResponseTrait;

    /**
     * Register a new company (tenant).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'nullable|string|max:100|unique:companies,subdomain',
            'domain' => 'nullable|string|max:255|unique:companies,domain',
            'subscription_plan_id' => 'nullable|uuid|exists:subscription_plans,id',
        ]);

        $company = DB::transaction(function () use ($validated) {
            $company = Company::create([
                'name' => $validated['name'],
                'subdomain' => $validated['subdomain'] ?? null,
                'domain' => $validated['domain'] ?? null,
                'subscription_plan_id' => $validated['subscription_plan_id'] ?? null,
                'is_active' => true,
            ]);

            // Automatically bootstrap default company settings
            CompanySetting::create([
                'company_id' => $company->id,
                'timezone' => 'UTC',
                'currency' => 'USD',
                'financial_year_start' => now()->startOfYear(),
                'financial_year_end' => now()->endOfYear(),
            ]);

            return $company;
        });

        return $this->successResponse($company, 'Company registered successfully.', 201);
    }

    /**
     * Retrieve current company profile.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $tenantContext = app(TenantContext::class);
        $company = $tenantContext->getCompany();

        if (!$company) {
            return $this->errorResponse('Tenant company context not resolved.', 400);
        }

        return $this->successResponse($company->load('settings'), 'Company profile retrieved.');
    }

    /**
     * Update current company profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $tenantContext = app(TenantContext::class);
        $company = $tenantContext->getCompany();

        if (!$company) {
            return $this->errorResponse('Tenant company context not resolved.', 400);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'subdomain' => 'sometimes|nullable|string|max:100|unique:companies,subdomain,' . $company->id,
            'domain' => 'sometimes|nullable|string|max:255|unique:companies,domain,' . $company->id,
        ]);

        $company->update($validated);

        return $this->successResponse($company, 'Company profile updated successfully.');
    }

    /**
     * Upload and update company logo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $tenantContext = app(TenantContext::class);
        $company = $tenantContext->getCompany();

        if (!$company) {
            return $this->errorResponse('Tenant company context not resolved.', 400);
        }

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->file('logo')) {
            // Delete old logo if exists
            if ($company->logo_url) {
                $oldPath = str_replace('/storage/', '', $company->logo_url);
                Storage::disk('public')->delete($oldPath);
            }

            // Store new logo
            $path = $request->file('logo')->store('logos/' . $company->id, 'public');
            $logoUrl = Storage::url($path);

            $company->update(['logo_url' => $logoUrl]);

            return $this->successResponse(['logo_url' => $logoUrl], 'Logo uploaded successfully.');
        }

        return $this->errorResponse('Failed to upload logo.', 400);
    }
}
