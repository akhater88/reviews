<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_branches', function (Blueprint $table) {
            $table->id();
            $table->string('google_place_id')->unique(); // UNIQUE for competition
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Saudi Arabia');
            $table->decimal('google_rating', 2, 1)->nullable();
            $table->unsignedInteger('google_reviews_count')->default(0);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->json('photos')->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('website', 500)->nullable();
            $table->json('opening_hours')->nullable();
            $table->json('types')->nullable();
            $table->timestamp('first_nominated_at')->nullable();
            $table->foreignId('first_nominated_by')->nullable()->constrained('competition_participants')->nullOnDelete();
            $table->unsignedInteger('total_nominations')->default(0);
            $table->unsignedInteger('total_periods_participated')->default(0);
            $table->unsignedInteger('times_won')->default(0);
            $table->timestamp('reviews_last_synced_at')->nullable();
            $table->unsignedInteger('reviews_synced_count')->default(0);
            $table->string('sync_status', 20)->default('pending');
            $table->text('sync_error')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_eligible')->default(true);
            $table->string('ineligible_reason')->nullable();
            $table->timestamps();

            $table->index(['city', 'is_active']);
            $table->index('google_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_branches');
    }
};
