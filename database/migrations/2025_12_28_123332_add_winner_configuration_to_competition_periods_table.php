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
        Schema::table('competition_periods', function (Blueprint $table) {
            // Winner selection tracking
            $table->boolean('winners_selected')->default(false)->after('winners_announced_at');
            $table->timestamp('winners_selected_at')->nullable()->after('winners_selected');

            // Winner announcement tracking
            $table->boolean('winners_announced')->default(false)->after('winners_selected_at');

            // Prize configuration for branches
            $table->decimal('first_prize', 10, 2)->default(2000)->after('prizes');
            $table->decimal('second_prize', 10, 2)->default(1500)->after('first_prize');
            $table->decimal('third_prize', 10, 2)->default(1000)->after('second_prize');

            // Lottery configuration for nominators
            $table->unsignedTinyInteger('nominator_winners_count')->default(5)->after('third_prize');
            $table->decimal('nominator_prize', 10, 2)->default(500)->after('nominator_winners_count');

            // Add index for status queries
            $table->index('winners_selected', 'periods_winners_selected_idx');
            $table->index('winners_announced', 'periods_winners_announced_idx');
        });

        // Also add winner_type and won_at to nominations table
        Schema::table('competition_nominations', function (Blueprint $table) {
            $table->string('winner_type', 30)->nullable()->after('is_winner');
            $table->decimal('prize_amount', 10, 2)->nullable()->after('winner_type');
            $table->timestamp('won_at')->nullable()->after('prize_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_periods', function (Blueprint $table) {
            $table->dropIndex('periods_winners_selected_idx');
            $table->dropIndex('periods_winners_announced_idx');

            $table->dropColumn([
                'winners_selected',
                'winners_selected_at',
                'winners_announced',
                'first_prize',
                'second_prize',
                'third_prize',
                'nominator_winners_count',
                'nominator_prize',
            ]);
        });

        Schema::table('competition_nominations', function (Blueprint $table) {
            $table->dropColumn(['winner_type', 'prize_amount', 'won_at']);
        });
    }
};
