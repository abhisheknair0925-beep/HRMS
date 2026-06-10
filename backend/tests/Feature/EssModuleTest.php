<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use App\Models\AttendanceLog;
use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EssModuleTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $user;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup company
        $this->company = Company::create([
            'name' => 'Acme ESS Corp',
            'is_active' => true,
        ]);

        // Setup user
        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Jane Employee',
            'email' => 'jane.e@acme.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Setup employee profile
        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'first_name' => 'Jane',
            'last_name' => 'Employee',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
            'phone' => '123-456-7890',
        ]);
    }

    /**
     * Test multi-tenant announcement scoping.
     */
    public function test_announcement_creation_and_scoping(): void
    {
        // 1. Create another tenant company and user
        $companyB = Company::create(['name' => 'Beta Corp', 'is_active' => true]);
        $userB = User::create([
            'company_id' => $companyB->id,
            'name' => 'Bob Beta',
            'email' => 'bob@beta.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $employeeB = Employee::create([
            'company_id' => $companyB->id,
            'user_id' => $userB->id,
            'first_name' => 'Bob',
            'last_name' => 'Beta',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
        ]);

        // 2. Create announcements for each company
        $announcementA = Announcement::create([
            'company_id' => $this->company->id,
            'title' => 'Acme Policy Update',
            'content' => 'Please read the new policy handbook.',
            'is_active' => true,
            'published_at' => now(),
        ]);

        $announcementB = Announcement::create([
            'company_id' => $companyB->id,
            'title' => 'Beta Holiday Notice',
            'content' => 'Office is closed next Friday.',
            'is_active' => true,
            'published_at' => now(),
        ]);

        // 3. Authenticate as User A (Acme) and query API
        \Laravel\Sanctum\Sanctum::actingAs($this->user);
        $responseA = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->getJson('/api/v1/ess/announcements');

        $responseA->assertStatus(200)
            ->assertJsonFragment(['title' => 'Acme Policy Update'])
            ->assertJsonMissing(['title' => 'Beta Holiday Notice']);

        // 4. Authenticate as User B (Beta) and query API
        \Laravel\Sanctum\Sanctum::actingAs($userB);
        $responseB = $this->withHeaders(['X-Company-ID' => $companyB->id])
            ->getJson('/api/v1/ess/announcements');

        $responseB->assertStatus(200)
            ->assertJsonFragment(['title' => 'Beta Holiday Notice'])
            ->assertJsonMissing(['title' => 'Acme Policy Update']);
    }

    /**
     * Test API endpoint for ESS dashboard structure.
     */
    public function test_ess_dashboard_data_structure(): void
    {
        // Create leave policy, balance and attendance log
        $policy = LeavePolicy::create([
            'company_id' => $this->company->id,
            'name' => 'Casual Leave',
            'total_days' => 10.0,
        ]);

        LeaveBalance::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'leave_policy_id' => $policy->id,
            'allocated_days' => 10.0,
            'used_days' => 1.0,
        ]);

        AttendanceLog::create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'log_date' => now()->toDateString(),
            'clock_in' => now()->startOfDay()->toDateTimeString(),
            'status' => 'Present',
        ]);

        Announcement::create([
            'company_id' => $this->company->id,
            'title' => 'Welcome to Portal',
            'content' => 'Enjoy your dashboard.',
            'is_active' => true,
            'published_at' => now(),
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($this->user);
        
        $response = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->getJson('/api/v1/ess/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'employee',
                    'today_attendance',
                    'leave_balances' => [
                        '*' => ['leave_policy']
                    ],
                    'announcements',
                ]
            ]);
    }

    /**
     * Test REST API profile updates.
     */
    public function test_ess_profile_update(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs($this->user);

        $response = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->putJson('/api/v1/ess/profile', [
                'phone' => '999-999-9999',
                'personal_info' => ['blood_group' => 'O+'],
                'bank_details' => ['bank_name' => 'Apex Bank'],
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('employees', [
            'id' => $this->employee->id,
            'phone' => '999-999-9999',
        ]);
    }

    /**
     * Test web portal browser routing and authentication.
     */
    public function test_ess_web_views_access(): void
    {
        // Unauthenticated access to dashboard redirects to login
        $response = $this->get('/ess/dashboard');
        $response->assertRedirect('/ess/login'); // Laravel default auth redirect

        // Authenticate user via Web session
        $this->actingAs($this->user);

        // Access dashboard
        $responseDash = $this->get('/ess/dashboard');
        $responseDash->assertStatus(200)
            ->assertSee('Jane');

        // Access attendance log view
        $responseAttend = $this->get('/ess/attendance');
        $responseAttend->assertStatus(200);

        // Access leave application page
        $responseLeave = $this->get('/ess/leave');
        $responseLeave->assertStatus(200);

        // Access documents center
        $responseDocs = $this->get('/ess/documents');
        $responseDocs->assertStatus(200);

        // Access profile editor
        $responseProfile = $this->get('/ess/profile');
        $responseProfile->assertStatus(200);
    }
}
