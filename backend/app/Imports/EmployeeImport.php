<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Employee;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToCollection, WithHeadingRow
{
    /**
     * Import rows to database collections.
     *
     * @param Collection $rows
     * @return void
     */
    public function collection(Collection $rows): void
    {
        $tenantContext = app(TenantContext::class);
        $companyId = $tenantContext->getCompanyId();

        if (!$companyId) {
            throw new \RuntimeException("Cannot process import: Tenant company context not resolved.");
        }

        DB::transaction(function () use ($rows, $companyId) {
            foreach ($rows as $row) {
                $email = $row['email'] ?? null;
                if (!$email) {
                    continue;
                }

                // Check if email already registered in system users
                $user = User::withoutGlobalScopes()->where('email', $email)->first();
                
                if (!$user) {
                    // 1. Create a user account
                    $user = User::create([
                        'id' => (string) Str::uuid(),
                        'company_id' => $companyId,
                        'name' => ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''),
                        'email' => $email,
                        'password' => Hash::make('HumaNodePass123!'), // Default password
                        'is_active' => true,
                    ]);
                    $user->assignRole('Employee');
                }

                // 2. Check if employee profile already exists
                $employee = Employee::where('email', $email)->first();

                if (!$employee) {
                    Employee::create([
                        'company_id' => $companyId,
                        'user_id' => $user->id,
                        'first_name' => $row['first_name'] ?? '',
                        'last_name' => $row['last_name'] ?? '',
                        'email' => $email,
                        'phone' => $row['phone'] ?? null,
                        'joining_date' => isset($row['joining_date']) ? now()->parse($row['joining_date']) : now(),
                        'status' => $row['status'] ?? 'Active',
                    ]);
                }
            }
        });
    }
}
