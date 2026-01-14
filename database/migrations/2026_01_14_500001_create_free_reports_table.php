<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_reports', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->string('place_id')->index();
            $table->string('business_name');
            $table->string('business_address')->nullable();
            $table->string('magic_link_token')->unique()->nullable();
            $table->timestamp('magic_link_expires_at')->nullable();
            $table->timestamp('magic_link_sent_at')->nullable();
            $table->enum('status', [
                'pending',
                'fetching_reviews',
                'analyzing',
                'generating_results',
                'completed',
                'failed'
            ])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['phone', 'place_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_reports');
    }
};
