<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_competition_benchmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('internal_competitions')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Period Type
            $table->enum('period_type', ['during_competition', 'before_competition']);
            $table->date('period_start');
            $table->date('period_end');

            // Metrics Snapshot
            $table->json('metrics');

            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(
                ['competition_id', 'tenant_id', 'branch_id', 'period_type'],
                'unique_benchmark'
            );
            $table->index(['competition_id', 'tenant_id'], 'idx_ic_bench_comp_tenant');
            $table->index(['competition_id', 'period_type'], 'idx_ic_bench_comp_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_competition_benchmarks');
    }
};
