<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GeneratedDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_document_is_saved_to_employee_locker(): void
    {
        $company = Company::create([
            'name' => 'Document Corp',
            'is_active' => true,
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Admin User',
            'email' => 'admin.docs@acme.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'company_id' => $company->id,
            'first_name' => 'Jane',
            'last_name' => 'Employee',
            'joining_date' => now()->toDateString(),
            'status' => 'Active',
        ]);

        Sanctum::actingAs($user);

        $response = $this->withHeaders(['X-Company-ID' => $company->id])
            ->postJson('/api/v1/documents/generate', [
                'employee_id' => $employee->id,
                'template' => 'offer',
                'title' => 'Offer Letter - Jane Employee',
                'content' => 'Generated offer letter content.',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Offer Letter - Jane Employee')
            ->assertJsonPath('data.type', 'offer');

        $this->assertDatabaseHas('employee_documents', [
            'employee_id' => $employee->id,
            'name' => 'Offer Letter - Jane Employee',
            'type' => 'offer',
        ]);
    }
}
