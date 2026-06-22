<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->string('name');
            $table->string('email');
            $table->string('role');
            $table->string('department');
            $table->date('joining_date');
            $table->string('status')->default('Incomplete');
            $table->string('emp_id')->nullable();
            $table->boolean('docs_verified')->default(false);
            $table->boolean('induction_scheduled')->default(false);
            $table->json('induction_details')->nullable();
            $table->json('assets')->nullable();
            $table->json('checklist')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_candidates');
    }
};
