<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_overviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            // Analysis identification
            $table->string('restaurant_id')->comment('Google Place ID');

            // Status tracking
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('current_step')->nullable()->comment('Current pipeline step');
            $table->unsignedTinyInteger('progress')->default(0)->comment('0-100 percentage');
            $table->text('error_message')->nullable();

            // Analysis period
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            // Metadata
            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('reviews_with_text')->default(0);
            $table->unsignedInteger('star_only_reviews')->default(0);

            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_processing_time')->nullable()->comment('seconds');
            $table->unsignedInteger('total_tokens_used')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'branch_id']);
            $table->index('status');
            $table->index('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_overviews');
    }
};
