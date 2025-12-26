<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->string('otp_code');
            $table->integer('attempts')->default(0);
            $table->integer('send_count')->default(1);
            $table->timestamp('expires_at');
            $table->timestamp('last_sent_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['phone', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
