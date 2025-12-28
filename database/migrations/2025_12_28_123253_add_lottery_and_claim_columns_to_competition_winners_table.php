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
        Schema::table('competition_winners', function (Blueprint $table) {
            // Make nomination_id and participant_id nullable for branch winners
            $table->foreignId('nomination_id')->nullable()->change();
            $table->foreignId('participant_id')->nullable()->change();
            $table->foreignId('competition_branch_id')->nullable()->change();

            // Add score reference
            $table->foreignId('competition_score_id')->nullable()->after('competition_branch_id')
                ->constrained('competition_scores')->nullOnDelete();

            // Winner type (branch for top 3, lottery for random nominators)
            $table->enum('winner_type', ['branch', 'lottery', 'branch_nominator'])->default('branch')->after('nomination_id');

            // Competition score at time of winning
            $table->decimal('competition_score', 8, 2)->nullable()->after('prize_details');

            // Lottery specific fields
            $table->string('lottery_ticket_number')->nullable()->unique()->after('competition_score');

            // Claim code for prize redemption
            $table->string('claim_code', 32)->nullable()->unique()->after('lottery_ticket_number');

            // Selected timestamp (when winner was determined)
            $table->timestamp('selected_at')->nullable()->after('claim_code');

            // Notification channels used
            $table->json('notification_channels')->nullable()->after('notification_error');

            // Bank details for prize transfer
            $table->string('bank_name', 100)->nullable()->after('claim_details');
            $table->string('iban', 34)->nullable()->after('bank_name');
            $table->string('transfer_reference', 100)->nullable()->after('iban');

            // Reminder tracking
            $table->timestamp('reminder_sent_at')->nullable()->after('transfer_reference');

            // Add index for claim lookups
            $table->index('claim_code', 'winners_claim_code_idx');
            $table->index('winner_type', 'winners_type_idx');
            $table->index('lottery_ticket_number', 'winners_lottery_num_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_winners', function (Blueprint $table) {
            $table->dropIndex('winners_claim_code_idx');
            $table->dropIndex('winners_type_idx');
            $table->dropIndex('winners_lottery_num_idx');

            $table->dropForeign(['competition_score_id']);
            $table->dropColumn([
                'competition_score_id',
                'winner_type',
                'competition_score',
                'lottery_ticket_number',
                'claim_code',
                'selected_at',
                'notification_channels',
                'bank_name',
                'iban',
                'transfer_reference',
                'reminder_sent_at',
            ]);
        });
    }
};
