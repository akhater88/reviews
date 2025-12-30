<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_competition_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('internal_competitions')->cascadeOnDelete();
            $table->foreignId('prize_id')->constrained('internal_competition_prizes')->cascadeOnDelete();

            // Winner Type
            $table->enum('winner_type', ['branch', 'employee']);

            // Winner References
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()
                  ->constrained('internal_competition_employees')->nullOnDelete();
            $table->string('employee_name')->nullable();

            // Final Results
            $table->enum('metric_type', ['employee_mentions', 'customer_satisfaction', 'response_time']);
            $table->decimal('final_score', 10, 4);
            $table->tinyInteger('final_rank')->unsigned();

            // Prize Status
            $table->enum('prize_status', ['announced', 'claimed', 'processing', 'delivered'])
                  ->default('announced');

            // Tracking Dates
            $table->timestamp('announced_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Delivery Details
            $table->text('delivery_notes')->nullable();
            $table->string('delivery_proof_path')->nullable();

            // Recipient Contact
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('recipient_address')->nullable();

            $table->timestamps();

            $table->unique(['competition_id', 'metric_type', 'final_rank'], 'unique_winner_per_metric_rank');
            $table->index(['competition_id', 'prize_status']);
            $table->index(['competition_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_competition_winners');
    }
};
