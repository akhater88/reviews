<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();

            // Pricing - SAR
            $table->decimal('price_monthly_sar', 10, 2)->default(0);
            $table->decimal('price_yearly_sar', 10, 2)->default(0);

            // Pricing - USD
            $table->decimal('price_monthly_usd', 10, 2)->default(0);
            $table->decimal('price_yearly_usd', 10, 2)->default(0);

            // Display
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_custom')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('color')->default('primary');
            $table->string('icon')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
