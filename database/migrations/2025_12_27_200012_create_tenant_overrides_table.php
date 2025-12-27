<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('override_type'); // feature, limit
            $table->string('key');
            $table->string('value');
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('granted_by')->nullable()->constrained('super_admins')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'override_type', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_overrides');
    }
};
