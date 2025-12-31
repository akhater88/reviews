<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_competition_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('internal_competitions')->cascadeOnDelete();

            // Prize Category
            $table->enum('metric_type', ['employee_mentions', 'customer_satisfaction', 'response_time']);
            $table->tinyInteger('rank')->unsigned();

            // Prize Details
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('image_path')->nullable();

            // Prize Type & Value
            $table->enum('prize_type', ['display', 'physical'])->default('display');
            $table->decimal('estimated_value', 10, 2)->nullable();
            $table->string('currency', 3)->default('SAR');

            // Physical Prize Details
            $table->json('physical_details')->nullable();

            $table->timestamps();

            $table->unique(['competition_id', 'metric_type', 'rank'], 'unique_prize_per_metric_rank');
            $table->index(['competition_id', 'metric_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_competition_prizes');
    }
};
