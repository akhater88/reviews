<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_competition_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('internal_competitions')->cascadeOnDelete();

            // Recipient
            $table->enum('recipient_type', ['super_admin', 'tenant_admin', 'branch_manager']);
            $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();

            // Channel & Event
            $table->enum('channel', ['whatsapp', 'email']);
            $table->enum('event_type', ['start', 'reminder', 'progress', 'ending_soon', 'ended', 'winner']);

            // Content
            $table->string('subject')->nullable();
            $table->text('content');
            $table->json('template_data')->nullable();

            // Status
            $table->enum('status', ['pending', 'sent', 'failed', 'skipped'])->default('pending');

            // Tracking
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('external_id')->nullable();

            $table->timestamps();

            $table->index(['competition_id', 'status'], 'idx_ic_notif_comp_status');
            $table->index(['scheduled_at', 'status'], 'idx_ic_notif_sched_status');
            $table->index(['recipient_user_id', 'event_type'], 'idx_ic_notif_user_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_competition_notifications');
    }
};
