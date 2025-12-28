<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix: Increase photo_url column size to accommodate long Google Places photo URLs
     * which can exceed 500 characters.
     */
    public function up(): void
    {
        Schema::table('competition_branches', function (Blueprint $table) {
            $table->text('photo_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_branches', function (Blueprint $table) {
            $table->string('photo_url', 500)->nullable()->change();
        });
    }
};
