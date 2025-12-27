<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competition_branch_id')->constrained()->cascadeOnDelete();
            $table->decimal('overall_rating', 3, 2)->default(0);
            $table->decimal('sentiment_score', 5, 2)->default(0);
            $table->decimal('response_rate', 5, 2)->default(0);
            $table->decimal('review_volume_score', 5, 2)->default(0);
            $table->decimal('trend_score', 5, 2)->default(0);
            $table->decimal('keyword_score', 5, 2)->default(0);
            $table->decimal('positive_ratio', 5, 2)->default(0);
            $table->decimal('negative_ratio', 5, 2)->default(0);
            $table->decimal('neutral_ratio', 5, 2)->default(0);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('reviews_this_period')->default(0);
            $table->decimal('competition_score', 5, 2)->default(0);
            $table->unsignedInteger('rank_position')->nullable();
            $table->unsignedInteger('nomination_count')->default(0);
            $table->unsignedInteger('reviews_analyzed')->default(0);
            $table->timestamp('last_analyzed_at')->nullable();
            $table->string('analysis_status', 20)->default('pending');
            $table->text('analysis_error')->nullable();
            $table->json('analysis_details')->nullable();
            $table->json('score_history')->nullable();
            $table->timestamps();

            $table->unique(['competition_period_id', 'competition_branch_id'], 'unique_score_per_period');
            $table->index(['competition_period_id', 'rank_position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_scores');
    }
};
