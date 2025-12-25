<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Branch name
            $table->string('name_ar')->nullable(); // Arabic name
            $table->string('google_place_id')->nullable(); // Google Places ID
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone')->nullable();
            $table->decimal('current_rating', 2, 1)->nullable(); // e.g., 4.5
            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('performance_score')->default(0); // 0-100
            $table->enum('status', ['excellent', 'good', 'average', 'needs_improvement'])->default('good');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('google_place_id');
            $table->index('status');
        });

        // Pivot table for branch-user relationship
        Schema::create('branch_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['branch_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_user');
        Schema::dropIfExists('branches');
    }
};
