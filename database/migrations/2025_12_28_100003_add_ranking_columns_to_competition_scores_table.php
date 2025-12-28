<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('competition_scores', 'previous_rank')) {
                $table->unsignedInteger('previous_rank')->nullable()->after('rank_position');
            }
            if (!Schema::hasColumn('competition_scores', 'rank_change')) {
                $table->integer('rank_change')->nullable()->after('previous_rank');
            }
            if (!Schema::hasColumn('competition_scores', 'rating_score')) {
                $table->decimal('rating_score', 5, 2)->default(0)->after('overall_rating');
            }
            if (!Schema::hasColumn('competition_scores', 'analyzed_reviews')) {
                $table->unsignedInteger('analyzed_reviews')->default(0)->after('total_reviews');
            }
        });
    }

    public function down(): void
    {
        Schema::table('competition_scores', function (Blueprint $table) {
            $table->dropColumn(['previous_rank', 'rank_change', 'rating_score', 'analyzed_reviews']);
        });
    }
};
