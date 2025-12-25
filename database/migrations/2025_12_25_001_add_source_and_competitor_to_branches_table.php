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
        Schema::table('branches', function (Blueprint $table) {
            // Source of the branch data
            $table->enum('source', ['google_business', 'manual'])->default('manual')->after('is_active');
            
            // Branch type: owned by user or competitor for comparison
            $table->enum('branch_type', ['owned', 'competitor'])->default('owned')->after('source');
            
            // For competitor branches: link to owned branch for comparison
            $table->foreignId('linked_branch_id')->nullable()->after('branch_type')
                  ->constrained('branches')->nullOnDelete();
            
            // Can reply to reviews (only for google_business source)
            $table->boolean('can_reply')->default(false)->after('linked_branch_id');
            
            // Last sync timestamp
            $table->timestamp('last_synced_at')->nullable()->after('can_reply');
            
            // Sync status
            $table->enum('sync_status', ['pending', 'syncing', 'completed', 'failed'])->default('pending')->after('last_synced_at');
            
            // Google Business specific fields
            $table->string('google_account_id')->nullable()->after('google_place_id');
            $table->string('google_location_id')->nullable()->after('google_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['linked_branch_id']);
            $table->dropColumn([
                'source',
                'branch_type', 
                'linked_branch_id',
                'can_reply',
                'last_synced_at',
                'sync_status',
                'google_account_id',
                'google_location_id'
            ]);
        });
    }
};
