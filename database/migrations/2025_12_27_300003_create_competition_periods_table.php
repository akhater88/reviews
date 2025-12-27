<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar');
            $table->string('slug', 100)->unique();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('analysis_started_at')->nullable();
            $table->timestamp('analysis_completed_at')->nullable();
            $table->timestamp('winners_announced_at')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedTinyInteger('winner_count')->default(10);
            $table->unsignedInteger('min_reviews_required')->default(10);
            $table->json('prizes')->nullable();
            $table->json('score_weights')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('winning_branch_id')->nullable()->constrained('competition_branches')->nullOnDelete();
            $table->decimal('winning_score', 5, 2)->nullable();
            $table->unsignedInteger('total_participants')->default(0);
            $table->unsignedInteger('total_nominations')->default(0);
            $table->unsignedInteger('total_branches')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('super_admins')->nullOnDelete();
            $table->timestamps();

            $table->unique(['year', 'month']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_periods');
    }
};
