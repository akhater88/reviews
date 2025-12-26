<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            // External IDs for duplicate prevention
            $table->string('google_review_id')->nullable();
            $table->string('outscraper_review_id')->nullable();

            // Author info
            $table->string('reviewer_name');
            $table->string('reviewer_photo_url')->nullable();
            $table->text('author_url')->nullable();

            // Review content
            $table->unsignedTinyInteger('rating'); // 1-5 stars
            $table->text('text')->nullable(); // Review text (can be empty)
            $table->string('language', 10)->default('ar');

            // Dates
            $table->timestamp('review_date'); // When review was posted on Google
            $table->timestamp('collected_at')->nullable(); // When we collected it

            // Source tracking
            $table->enum('source', ['google_business', 'outscraper'])->default('outscraper');

            // Owner reply
            $table->text('owner_reply')->nullable();
            $table->timestamp('owner_reply_date')->nullable();
            $table->boolean('replied_via_tabsense')->default(false);

            // AI Analysis fields
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->nullable();
            $table->decimal('sentiment_score', 4, 2)->nullable(); // -1.00 to 1.00
            $table->text('ai_summary')->nullable();
            $table->json('categories')->nullable(); // ['food', 'service', 'price', 'ambiance']
            $table->json('keywords')->nullable(); // Extracted keywords
            $table->enum('reviewer_gender', ['male', 'female', 'unknown'])->nullable();

            // Quality & filtering
            $table->decimal('quality_score', 3, 2)->default(0.80);
            $table->boolean('is_spam')->default(false);
            $table->boolean('is_hidden')->default(false);

            // Reply status
            $table->boolean('is_replied')->default(false);
            $table->boolean('needs_reply')->default(true);

            // Metadata (likes, author_id, etc.)
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'branch_id']);
            $table->index(['branch_id', 'review_date']);
            $table->index('source');
            $table->index('sentiment');
            $table->index('rating');
            $table->index('is_replied');
            $table->index('review_date');

            // Unique constraints to prevent duplicates
            $table->unique(['branch_id', 'google_review_id'], 'unique_google_review');
            $table->unique(['branch_id', 'outscraper_review_id'], 'unique_outscraper_review');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
