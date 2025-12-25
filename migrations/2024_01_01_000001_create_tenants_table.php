<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Business name
            $table->string('name_ar')->nullable(); // Arabic business name
            $table->string('slug')->unique(); // URL-friendly identifier
            $table->string('logo')->nullable(); // Logo path
            $table->string('email')->nullable(); // Primary contact email
            $table->string('phone')->nullable();
            $table->enum('subscription_plan', ['trial', 'basic', 'pro', 'enterprise'])->default('trial');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Flexible settings storage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
