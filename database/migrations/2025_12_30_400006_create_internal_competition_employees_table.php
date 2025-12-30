<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_competition_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('internal_competitions')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();

            // Employee Info
            $table->string('employee_name');
            $table->string('normalized_name');

            // Mention Counts
            $table->unsignedInteger('total_mentions')->default(0);
            $table->unsignedInteger('positive_mentions')->default(0);
            $table->unsignedInteger('negative_mentions')->default(0);
            $table->unsignedInteger('neutral_mentions')->default(0);

            // Score & Rank
            $table->decimal('score', 10, 4)->default(0);
            $table->unsignedInteger('rank')->nullable();

            // Sample Mentions
            $table->json('sample_positive_mentions')->nullable();
            $table->json('sample_negative_mentions')->nullable();

            // Tracking
            $table->date('first_mention_at')->nullable();
            $table->date('last_mention_at')->nullable();
            $table->boolean('is_final')->default(false);
            $table->timestamp('calculated_at')->nullable();

            $table->timestamps();

            $table->unique(['competition_id', 'branch_id', 'normalized_name'], 'unique_employee_per_branch');
            $table->index(['competition_id', 'score'], 'idx_employee_ranking');
            $table->index(['competition_id', 'branch_id']);
            $table->index(['competition_id', 'tenant_id']);
            $table->index('normalized_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_competition_employees');
    }
};
