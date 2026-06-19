<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that missing X-Company-ID header throws a tenant validation error.
     */
    public function test_missing_tenant_header_fails(): void
    {
        // Create a user with null company_id (Super Admin / Global User context)
        $user = User::create([
            'company_id' => null,
            'name' => 'Super Admin',
            'email' => 'super.admin@acme.com',
            'password' => bcrypt('password'),
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/company');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Missing multi-tenant context boundary. Please provide X-Company-ID header.',
            ]);
    }

    /**
     * Test that branches are strictly isolated based on company header context.
     */
    public function test_tenant_branch_isolation(): void
    {
        // 1. Create Company A and Company B
        $companyA = Company::create(['name' => 'Company A', 'is_active' => true]);
        $companyB = Company::create(['name' => 'Company B', 'is_active' => true]);

        // 2. Create users for Company A and Company B
        $userA = User::create([
            'company_id' => $companyA->id,
            'name' => 'User A',
            'email' => 'user.a@acme.com',
            'password' => bcrypt('password'),
        ]);

        $userB = User::create([
            'company_id' => $companyB->id,
            'name' => 'User B',
            'email' => 'user.b@acme.com',
            'password' => bcrypt('password'),
        ]);

        // 3. Create branches for Company A
        $branchA1 = Branch::create(['company_id' => $companyA->id, 'name' => 'Branch A1', 'code' => 'BR-A1']);
        $branchA2 = Branch::create(['company_id' => $companyA->id, 'name' => 'Branch A2', 'code' => 'BR-A2']);

        // 4. Create branches for Company B
        $branchB = Branch::create(['company_id' => $companyB->id, 'name' => 'Branch B1', 'code' => 'BR-B1']);

        // 5. Request Branch list as Company A user
        \Laravel\Sanctum\Sanctum::actingAs($userA);
        $responseA = $this->withHeaders(['X-Company-ID' => $companyA->id])
            ->getJson('/api/v1/branches');

        $responseA->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Branch A1'])
            ->assertJsonFragment(['name' => 'Branch A2'])
            ->assertJsonMissing(['name' => 'Branch B1']);

        // 6. Request Branch list as Company B user
        \Laravel\Sanctum\Sanctum::actingAs($userB);
        $responseB = $this->withHeaders(['X-Company-ID' => $companyB->id])
            ->getJson('/api/v1/branches');

        $responseB->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Branch B1'])
            ->assertJsonMissing(['name' => 'Branch A1']);
    }

    /**
     * Test that company settings are isolated and correctly updated.
     */
    public function test_company_settings_isolation(): void
    {
        $company = Company::create(['name' => 'Acme Corp', 'is_active' => true]);
        
        CompanySetting::create([
            'company_id' => $company->id,
            'timezone' => 'UTC',
            'currency' => 'USD',
        ]);

        // Seed Admin role and assign to user to satisfy function-level authorization checks
        \Spatie\Permission\Models\Role::create(['name' => 'Admin', 'guard_name' => 'web']);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Test User',
            'email' => 'test@acme.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('Admin');

        \Laravel\Sanctum\Sanctum::actingAs($user);

        $response = $this->withHeaders(['X-Company-ID' => $company->id])
            ->putJson('/api/v1/settings', [
                'timezone' => 'Asia/Dubai',
                'currency' => 'AED',
                'financial_year_start' => '2026-01-01',
                'financial_year_end' => '2026-12-31',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'timezone' => 'Asia/Dubai',
                'currency' => 'AED',
            ]);
    }
}
