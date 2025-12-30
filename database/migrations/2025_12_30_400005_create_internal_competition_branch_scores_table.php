<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_competition_branch_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('internal_competitions')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();

            // Metric Type (only branch-level metrics)
            $table->enum('metric_type', ['customer_satisfaction', 'response_time']);

            // Score Data
            $table->decimal('score', 10, 4)->default(0);
            $table->unsignedInteger('rank')->nullable();

            // Score Breakdown
            $table->json('score_breakdown')->nullable();

            // Period
            $table->date('period_start');
            $table->date('period_end');

            // Status
            $table->boolean('is_final')->default(false);
            $table->timestamp('calculated_at');

            $table->timestamps();

            $table->unique(['competition_id', 'branch_id', 'metric_type'], 'unique_branch_metric_score');
            $table->index(['competition_id', 'metric_type', 'score'], 'idx_ranking');
            $table->index(['competition_id', 'tenant_id']);
            $table->index('is_final');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_competition_branch_scores');
    }
};
