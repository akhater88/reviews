<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_overview_id')->constrained()->cascadeOnDelete();
            $table->string('restaurant_id')->comment('Google Place ID');

            // Analysis type
            $table->string('analysis_type')->comment('sentiment, recommendations, keywords, etc.');

            // Result data
            $table->json('result')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');

            // AI Provider tracking
            $table->string('provider')->nullable()->comment('openai or anthropic');
            $table->string('model')->nullable()->comment('gpt-4o, claude-3-5-sonnet, etc.');

            // Metrics
            $table->unsignedInteger('processing_time')->nullable()->comment('seconds');
            $table->unsignedInteger('tokens_used')->default(0);
            $table->decimal('confidence', 3, 2)->default(0.85);

            // Context
            $table->unsignedInteger('review_count')->default(0);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['analysis_overview_id', 'analysis_type']);
            $table->index('analysis_type');
            $table->index('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
};
