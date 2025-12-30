<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_competitions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Creator Info
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->enum('created_by_type', ['super_admin', 'tenant_admin'])->default('tenant_admin');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();

            // Basic Info
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('cover_image')->nullable();

            // Competition Type
            $table->enum('scope', ['single_tenant', 'multi_tenant'])->default('single_tenant');
            $table->enum('period_type', ['monthly', 'quarterly'])->default('monthly');

            // Dates
            $table->date('start_date');
            $table->date('end_date');

            // Status
            $table->enum('status', ['draft', 'active', 'calculating', 'ended', 'published', 'cancelled'])
                  ->default('draft');

            // Metrics Configuration
            $table->json('metrics_config');

            // Visibility Settings
            $table->enum('leaderboard_visibility', ['always', 'after_end', 'hidden'])->default('after_end');
            $table->boolean('show_progress_hints')->default(false);
            $table->boolean('public_showcase')->default(false);

            // Notification Settings
            $table->json('notification_settings')->nullable();

            // Timestamps
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('tenant_id');
            $table->index(['start_date', 'end_date']);
            $table->index('scope');
            $table->index('created_by_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_competitions');
    }
};
