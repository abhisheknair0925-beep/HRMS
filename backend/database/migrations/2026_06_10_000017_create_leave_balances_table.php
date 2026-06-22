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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('leave_policy_id')->index();
            
            $table->decimal('allocated_days', 4, 1)->default(0.0);
            $table->decimal('used_days', 4, 1)->default(0.0);
            $table->decimal('encashed_days', 4, 1)->default(0.0);
            
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

            // Unique leave balance record per employee and policy
            $table->unique(['employee_id', 'leave_policy_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
