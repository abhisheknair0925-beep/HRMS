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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('leave_policy_id')->index();
            
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('half_day')->default(false);
            $table->decimal('total_days', 4, 1);
            $table->text('reason');
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->uuid('approved_by')->nullable()->index(); // references users.id
            $table->text('rejection_reason')->nullable();
            
            // Audits
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();

            $table->foreign('leave_policy_id')
                ->references('id')
                ->on('leave_policies')
                ->cascadeOnDelete();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
