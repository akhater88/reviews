<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_report_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('free_report_id')->constrained()->onDelete('cascade');
            $table->decimal('overall_score', 3, 1)->nullable();
            $table->integer('total_reviews')->default(0);
            $table->decimal('average_rating', 2, 1)->nullable();
            $table->json('sentiment_breakdown')->nullable();
            $table->json('category_scores')->nullable();
            $table->json('top_strengths')->nullable();
            $table->json('top_weaknesses')->nullable();
            $table->json('keyword_analysis')->nullable();
            $table->text('executive_summary')->nullable();
            $table->json('recommendations')->nullable();
            $table->timestamps();

            $table->unique('free_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_report_results');
    }
};
