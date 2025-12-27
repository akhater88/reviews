<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_replies', function (Blueprint $table) {
            if (!Schema::hasColumn('review_replies', 'status')) {
                $table->string('status')->default('draft')->after('ai_provider');
            }
            if (!Schema::hasColumn('review_replies', 'ai_model')) {
                $table->string('ai_model')->nullable()->after('ai_provider');
            }
            if (!Schema::hasColumn('review_replies', 'error_message')) {
                $table->text('error_message')->nullable()->after('google_reply_id');
            }
            if (!Schema::hasColumn('review_replies', 'tokens_used')) {
                $table->unsignedInteger('tokens_used')->default(0)->after('error_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('review_replies', function (Blueprint $table) {
            $columns = ['status', 'ai_model', 'error_message', 'tokens_used'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('review_replies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
