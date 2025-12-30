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
        Schema::table('internal_competitions', function (Blueprint $table) {
            // Add column to track if it's auto-enroll or manual selection
            $table->string('tenant_enrollment_mode')->default('manual')->after('scope');
            // manual = select specific tenants
            // auto_all = automatically enroll all tenants
            // auto_new = automatically enroll new tenants that join later
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_competitions', function (Blueprint $table) {
            $table->dropColumn('tenant_enrollment_mode');
        });
    }
};
