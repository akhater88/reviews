<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_participants', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('verification_code', 10)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();
            $table->unsignedTinyInteger('verification_attempts')->default(0);
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('city', 100)->nullable();
            $table->boolean('whatsapp_opted_in')->default(true);
            $table->boolean('sms_opted_in')->default(false);
            $table->string('referral_code', 20)->unique()->nullable();
            $table->foreignId('referred_by_id')->nullable()->constrained('competition_participants')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('source', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();

            $table->index(['phone', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_participants');
    }
};
