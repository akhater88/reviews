<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Who created the reply
            $table->text('reply_text');
            $table->boolean('is_ai_generated')->default(false);
            $table->string('ai_tone')->nullable(); // 'professional', 'friendly', 'apologetic', etc.
            $table->string('ai_provider')->nullable(); // 'openai' or 'anthropic'
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable(); // When published to Google
            $table->string('google_reply_id')->nullable(); // Google's reply ID after publishing
            $table->timestamps();

            $table->index('review_id');
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_replies');
    }
};
