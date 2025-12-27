<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('period_month');
            $table->unsignedSmallInteger('period_year');

            // Usage counters
            $table->unsignedInteger('ai_replies_used')->default(0);
            $table->unsignedBigInteger('ai_tokens_used')->default(0);
            $table->unsignedInteger('api_calls_used')->default(0);
            $table->unsignedInteger('reviews_synced')->default(0);
            $table->unsignedInteger('analysis_runs')->default(0);

            // Snapshot of resources
            $table->unsignedInteger('branches_count')->default(0);
            $table->unsignedInteger('competitors_count')->default(0);
            $table->unsignedInteger('users_count')->default(0);

            $table->timestamps();

            $table->unique(['tenant_id', 'period_month', 'period_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_summaries');
    }
};
