<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminWorkflowApiTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Admin Workflow Corp',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'company_id' => $this->company->id,
            'name' => 'Admin User',
            'email' => 'admin.workflow@acme.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'first_name' => 'Jane',
            'last_name' => 'Employee',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
        ]);

        Sanctum::actingAs($this->admin);
    }

    public function test_admin_can_create_holiday(): void
    {
        $response = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->postJson('/api/v1/holidays', [
                'name' => 'Founders Day',
                'holiday_date' => '2026-08-01',
                'type' => 'Corporate Closed',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Founders Day');

        $this->assertDatabaseHas('company_holidays', [
            'company_id' => $this->company->id,
            'name' => 'Founders Day',
        ]);
    }

    public function test_admin_can_process_comp_off_request(): void
    {
        $create = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->postJson('/api/v1/comp-offs', [
                'employee_id' => $this->employee->id,
                'worked_date' => '2026-06-13',
                'reason' => 'Weekend release support',
            ]);

        $create->assertCreated()
            ->assertJsonPath('data.status', 'Pending');

        $id = $create->json('data.id');

        $approve = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->postJson("/api/v1/comp-offs/{$id}/approve");

        $approve->assertOk()
            ->assertJsonPath('data.status', 'Approved');
    }

    public function test_admin_can_save_performance_review_and_message(): void
    {
        $review = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->postJson('/api/v1/performance-reviews', [
                'employee_id' => $this->employee->id,
                'overall_score' => 4.5,
                'metrics' => [
                    ['name' => 'Quality', 'score' => 4.5],
                    ['name' => 'Productivity', 'score' => 4.5],
                ],
                'comment' => 'Strong performance.',
            ]);

        $review->assertCreated()
            ->assertJsonPath('data.overall', 4.5);

        $message = $this->withHeaders(['X-Company-ID' => $this->company->id])
            ->postJson("/api/v1/employees/{$this->employee->id}/messages", [
                'message' => 'Please review your scorecard.',
                'sender_type' => 'admin',
            ]);

        $message->assertCreated()
            ->assertJsonPath('data.text', 'Please review your scorecard.');
    }
}
