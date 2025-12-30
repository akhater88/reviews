<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_competition_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('internal_competitions')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();

            $table->timestamp('enrolled_at')->useCurrent();
            $table->foreignId('enrolled_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'withdrawn'])->default('active');
            $table->timestamp('withdrawn_at')->nullable();
            $table->text('withdrawal_reason')->nullable();

            $table->timestamps();

            $table->unique(['competition_id', 'branch_id'], 'unique_competition_branch');
            $table->index(['competition_id', 'tenant_id']);
            $table->index(['competition_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_competition_branches');
    }
};
