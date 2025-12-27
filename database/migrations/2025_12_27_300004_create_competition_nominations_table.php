<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_nominations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('competition_participants')->cascadeOnDelete();
            $table->foreignId('competition_branch_id')->constrained()->cascadeOnDelete();
            $table->timestamp('nominated_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('source', 50)->nullable();
            $table->boolean('is_valid')->default(true);
            $table->string('invalidation_reason')->nullable();
            $table->timestamp('invalidated_at')->nullable();
            $table->foreignId('invalidated_by')->nullable()->constrained('super_admins')->nullOnDelete();
            $table->boolean('is_winner')->default(false);
            $table->unsignedTinyInteger('prize_rank')->nullable();
            $table->timestamps();

            $table->unique(['competition_period_id', 'participant_id'], 'unique_nomination_per_period');
            $table->index(['competition_period_id', 'competition_branch_id'], 'idx_period_branch');
            $table->index('is_valid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_nominations');
    }
};
