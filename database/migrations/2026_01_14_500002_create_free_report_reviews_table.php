<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_report_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('free_report_id')->constrained()->onDelete('cascade');
            $table->string('review_id')->index();
            $table->string('author_name')->nullable();
            $table->string('author_image')->nullable();
            $table->integer('rating');
            $table->text('text')->nullable();
            $table->timestamp('review_time')->nullable();
            $table->string('language')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['free_report_id', 'review_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_report_reviews');
    }
};
