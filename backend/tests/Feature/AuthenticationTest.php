<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create company
        $this->company = Company::create([
            'name' => 'Acme Corp',
            'is_active' => true,
        ]);

        // 2. Setup standard role
        Role::create(['name' => 'Employee', 'guard_name' => 'web']);

        // 3. Create active employee user
        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'John Doe',
            'email' => 'john.doe@acme.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->user->assignRole('Employee');
    }

    /**
     * Test successful login returns token and roles.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'john.doe@acme.com',
            'password' => 'password123',
            'device_name' => 'TestDevice',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'access_token',
                    'roles',
                    'permissions',
                ]
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'name' => 'TestDevice',
        ]);

        // Verify audit log exists
        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $this->user->id,
            'description' => 'User logged in successfully',
        ]);
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'john.doe@acme.com',
            'password' => 'wrongpassword',
            'device_name' => 'TestDevice',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid email address or password credentials.',
            ]);
    }

    /**
     * Test logout revokes token.
     */
    public function test_user_can_logout(): void
    {
        $token = $this->user->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Company-ID' => $this->company->id,
        ])->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    /**
     * Test change password forces session revocation.
     */
    public function test_user_can_change_password(): void
    {
        $token = $this->user->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Company-ID' => $this->company->id,
        ])->postJson('/api/v1/change-password', [
            'old_password' => 'password123',
            'new_password' => 'newsecurepassword789',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password updated successfully. Active sessions revoked.',
            ]);

        // Access token must be deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);

        // Reload user and verify password updated
        $this->user->refresh();
        $this->assertTrue(Hash::check('newsecurepassword789', $this->user->password));
    }
}
