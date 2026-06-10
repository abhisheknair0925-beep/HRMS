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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly, etc.
            $table->integer('employee_limit')->default(10); // default tier limit
            $table->jsonb('features')->nullable(); // Active modules / features list
            
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
        Schema::dropIfExists('subscription_plans');
    }
};
