<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('google_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            // OAuth tokens
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('token_expires_at');
            
            // Google account info
            $table->string('google_email')->nullable();
            $table->string('google_account_id')->nullable();
            $table->string('google_account_name')->nullable();
            
            // Scopes granted
            $table->json('scopes')->nullable();
            
            // Connection status
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            
            $table->timestamps();
            
            // One token per tenant
            $table->unique('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_tokens');
    }
};
