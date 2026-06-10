<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->uuid('user_id')->nullable()->unique(); // Linked user account
            
            $table->string('employee_id')->index(); // e.g. EMP-2026-0001
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('profile_picture_url')->nullable();
            
            // Personal demographics
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('marital_status')->nullable();
            
            // Employment details
            $table->date('joining_date');
            $table->string('status')->default('Probation'); // Active, Probation, Suspended, Terminated
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('designation_id')->nullable()->index();

            // JSONB structural attributes
            $table->jsonb('personal_info')->nullable(); // passport, national_id, address
            $table->jsonb('family_info')->nullable(); // array of family members
            $table->jsonb('emergency_contacts')->nullable(); // array of emergency contacts
            $table->jsonb('bank_details')->nullable(); // bank_name, account_number, etc.

            // Audit & Soft Deletes
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Constraint: Employee ID must be unique per company
            $table->unique(['company_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
