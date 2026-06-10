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
        Schema::create('employee_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('employee_id')->index();
            
            $table->uuid('old_department_id')->nullable()->index();
            $table->uuid('new_department_id')->nullable()->index();
            $table->uuid('old_designation_id')->nullable()->index();
            $table->uuid('new_designation_id')->nullable()->index();
            
            $table->date('transfer_date');
            $table->string('reason')->nullable();
            
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

            $table->foreign('old_department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('new_department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('old_designation_id')
                ->references('id')
                ->on('designations')
                ->nullOnDelete();

            $table->foreign('new_designation_id')
                ->references('id')
                ->on('designations')
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
        Schema::dropIfExists('employee_transfers');
    }
};
