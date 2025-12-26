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
        // Add website column if it doesn't exist
        if (!Schema::hasColumn('branches', 'website')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->string('website')->nullable()->after('phone');
            });
        }

        // Rename latitude to lat if latitude exists
        if (Schema::hasColumn('branches', 'latitude') && !Schema::hasColumn('branches', 'lat')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->renameColumn('latitude', 'lat');
            });
        }

        // Rename longitude to lng if longitude exists
        if (Schema::hasColumn('branches', 'longitude') && !Schema::hasColumn('branches', 'lng')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->renameColumn('longitude', 'lng');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove website column if it exists
        if (Schema::hasColumn('branches', 'website')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('website');
            });
        }

        // Rename lat back to latitude if lat exists
        if (Schema::hasColumn('branches', 'lat') && !Schema::hasColumn('branches', 'latitude')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->renameColumn('lat', 'latitude');
            });
        }

        // Rename lng back to longitude if lng exists
        if (Schema::hasColumn('branches', 'lng') && !Schema::hasColumn('branches', 'longitude')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->renameColumn('lng', 'longitude');
            });
        }
    }
};
