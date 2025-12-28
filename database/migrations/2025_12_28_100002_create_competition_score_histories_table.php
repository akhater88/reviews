<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_score_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_score_id')->constrained()->cascadeOnDelete();
            $table->decimal('competition_score', 8, 2);
            $table->integer('rank_position')->nullable();
            $table->decimal('rating_score', 8, 2)->nullable();
            $table->decimal('sentiment_score', 8, 2)->nullable();
            $table->decimal('response_rate', 8, 2)->nullable();
            $table->decimal('volume_score', 8, 2)->nullable();
            $table->decimal('trend_score', 8, 2)->nullable();
            $table->decimal('keyword_score', 8, 2)->nullable();
            $table->timestamp('recorded_at');

            $table->index(['competition_score_id', 'recorded_at'], 'score_history_score_recorded_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_score_histories');
    }
};
