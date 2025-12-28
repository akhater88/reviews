<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_branch_id')->constrained()->cascadeOnDelete();
            $table->string('google_review_id')->unique();
            $table->string('reviewer_name')->nullable();
            $table->string('reviewer_photo_url')->nullable();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('review_text')->nullable();
            $table->timestamp('review_date')->nullable();
            $table->unsignedInteger('review_likes')->default(0);
            $table->boolean('has_owner_response')->default(false);
            $table->text('owner_response')->nullable();
            $table->timestamp('owner_response_date')->nullable();
            $table->string('review_language', 10)->default('ar');
            $table->json('review_photos')->nullable();
            $table->decimal('sentiment_score', 5, 2)->nullable();
            $table->string('sentiment_label', 20)->nullable();
            $table->json('keywords')->nullable();
            $table->json('categories')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->index(['competition_branch_id', 'review_date'], 'comp_reviews_branch_date_idx');
            $table->index('sentiment_score', 'comp_reviews_sentiment_idx');
            $table->index('analyzed_at', 'comp_reviews_analyzed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_reviews');
    }
};
