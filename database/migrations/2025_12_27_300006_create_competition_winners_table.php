<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competition_branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('competition_participants')->cascadeOnDelete();
            $table->foreignId('nomination_id')->constrained('competition_nominations')->cascadeOnDelete();
            $table->unsignedTinyInteger('prize_rank');
            $table->decimal('prize_amount', 10, 2)->nullable();
            $table->string('prize_currency', 3)->default('SAR');
            $table->string('prize_description')->nullable();
            $table->json('prize_details')->nullable();
            $table->boolean('prize_claimed')->default(false);
            $table->timestamp('prize_claimed_at')->nullable();
            $table->string('claim_method', 50)->nullable();
            $table->json('claim_details')->nullable();
            $table->boolean('is_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->string('notification_method', 50)->nullable();
            $table->unsignedTinyInteger('notification_attempts')->default(0);
            $table->text('notification_error')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('super_admins')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->unique(['competition_period_id', 'participant_id'], 'unique_winner_per_period');
            $table->index('prize_claimed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_winners');
    }
};
