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
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->string('name'); // e.g. Annual Leave, Medical Leave, Casual Leave
            $table->decimal('total_days', 4, 1);
            $table->decimal('carry_over_max', 4, 1)->default(0.0);
            $table->string('accrual_rate')->nullable(); // monthly, quarterly, yearly
            
            // Audits & Soft Deletes
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            // Unique name per company
            $table->unique(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
    }
};
