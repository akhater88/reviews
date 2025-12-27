<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();

            // Resource Limits (-1 = unlimited)
            $table->integer('max_branches')->default(1);
            $table->integer('max_competitors')->default(0);
            $table->integer('max_users')->default(1);

            // Usage Limits (per month, -1 = unlimited)
            $table->integer('max_reviews_sync')->default(100);
            $table->integer('max_ai_replies')->default(10);
            $table->integer('max_ai_tokens')->default(10000);
            $table->integer('max_api_calls')->default(100);
            $table->integer('max_analysis_runs')->default(5);

            // Data Retention (days, -1 = unlimited)
            $table->integer('analysis_retention_days')->default(30);

            $table->timestamps();

            $table->unique('plan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_limits');
    }
};
