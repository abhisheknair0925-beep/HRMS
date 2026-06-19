<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_appraisals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('reviewer_name');
            $table->date('review_date');
            $table->decimal('overall_score', 3, 2);
            $table->integer('quality_score');
            $table->integer('productivity_score');
            $table->integer('teamwork_score');
            $table->integer('communication_score');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // Seed default appraisals for testing
        $employees = DB::table('employees')->get();
        foreach ($employees as $employee) {
            if ($employee->employee_id === 'EMP001') {
                DB::table('performance_appraisals')->insert([
                    'id' => Str::uuid()->toString(),
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'reviewer_name' => 'Executive Board',
                    'review_date' => '2026-05-15',
                    'overall_score' => 4.3,
                    'quality_score' => 5,
                    'productivity_score' => 4,
                    'teamwork_score' => 4,
                    'communication_score' => 4,
                    'comment' => 'Sarah is an outstanding manager who guides the product lifecycle effectively. Her communication with stakeholders is top-tier.',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif ($employee->employee_id === 'EMP002') {
                DB::table('performance_appraisals')->insert([
                    'id' => Str::uuid()->toString(),
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'reviewer_name' => 'Sarah Manager',
                    'review_date' => '2026-06-01',
                    'overall_score' => 4.5,
                    'quality_score' => 5,
                    'productivity_score' => 5,
                    'teamwork_score' => 4,
                    'communication_score' => 4,
                    'comment' => 'John shows incredible frontend skills, maintaining clean React architectures. Very reliable and quick turnaround times.',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif ($employee->employee_id === 'EMP003') {
                DB::table('performance_appraisals')->insert([
                    'id' => Str::uuid()->toString(),
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'reviewer_name' => 'Executive Operations',
                    'review_date' => '2026-04-10',
                    'overall_score' => 4.0,
                    'quality_score' => 4,
                    'productivity_score' => 4,
                    'teamwork_score' => 5,
                    'communication_score' => 3,
                    'comment' => 'Sarah HR maintains excellent company culture and tenant branch guidelines. Communication could be optimized during crunch workflows.',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif ($employee->employee_id === 'EMP004') {
                DB::table('performance_appraisals')->insert([
                    'id' => Str::uuid()->toString(),
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'reviewer_name' => 'Internal Audit',
                    'review_date' => '2026-03-20',
                    'overall_score' => 4.8,
                    'quality_score' => 5,
                    'productivity_score' => 5,
                    'teamwork_score' => 4,
                    'communication_score' => 5,
                    'comment' => 'Maintains server architecture flawlessly. Quick resolution of docker/SaaS geofence routing checkpoints.',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_appraisals');
    }
};
