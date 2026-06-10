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
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('subdomain')->unique()->nullable();
            $table->string('domain')->unique()->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            
            // SaaS billing linkages
            $table->uuid('subscription_plan_id')->nullable()->index();
            $table->timestamp('subscription_ends_at')->nullable();
            
            // Audit Logging & Soft Deletes
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
