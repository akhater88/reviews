<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_branches', function (Blueprint $table) {
            if (!Schema::hasColumn('competition_branches', 'reviews_synced_count')) {
                $table->unsignedInteger('reviews_synced_count')->default(0)->after('reviews_last_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('competition_branches', function (Blueprint $table) {
            $table->dropColumn(['reviews_synced_count']);
        });
    }
};
