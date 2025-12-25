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
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('google_review_id')->unique(); // Google's review ID
            $table->string('reviewer_name');
            $table->string('reviewer_photo_url')->nullable();
            $table->unsignedTinyInteger('rating'); // 1-5 stars
            $table->text('text')->nullable(); // Review text (can be empty)
            $table->timestamp('review_date'); // When review was posted on Google
            $table->string('language', 10)->nullable(); // Detected language
            
            // AI Analysis fields
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->nullable();
            $table->decimal('sentiment_score', 3, 2)->nullable(); // -1.00 to 1.00
            $table->json('categories')->nullable(); // ['food', 'service', 'price', 'ambiance']
            $table->json('keywords')->nullable(); // Extracted keywords
            $table->enum('reviewer_gender', ['male', 'female', 'unknown'])->nullable();
            
            // Reply status
            $table->boolean('is_replied')->default(false);
            $table->boolean('needs_reply')->default(true);
            
            $table->timestamps();

            $table->index('branch_id');
            $table->index('rating');
            $table->index('sentiment');
            $table->index('is_replied');
            $table->index('review_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
