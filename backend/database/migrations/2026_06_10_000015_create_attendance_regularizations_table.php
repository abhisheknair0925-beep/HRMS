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
        Schema::create('attendance_regularizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('attendance_log_id')->nullable()->index();
            $table->uuid('employee_id')->index();
            
            $table->date('requested_date');
            $table->timestamp('requested_clock_in')->nullable();
            $table->timestamp('requested_clock_out')->nullable();
            $table->text('reason');
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->uuid('approved_by')->nullable()->index(); // references users.id
            
            // Audits
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            $table->foreign('attendance_log_id')
                ->references('id')
                ->on('attendance_logs')
                ->nullOnDelete();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
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
        Schema::dropIfExists('attendance_regularizations');
    }
};
