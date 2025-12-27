<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('action');

            $table->foreignId('old_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->foreignId('new_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();

            // Who made the change
            $table->string('changed_by_type')->nullable(); // super_admin, tenant, system
            $table->unsignedBigInteger('changed_by_id')->nullable();

            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['subscription_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_history');
    }
};
