<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add tenant_id as nullable first (no foreign key yet)
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // Step 2: Populate tenant_id from branch relationship
        DB::statement('
            UPDATE reviews
            SET tenant_id = (
                SELECT tenant_id FROM branches WHERE branches.id = reviews.branch_id
            )
            WHERE tenant_id IS NULL
        ');

        // Step 3: Make tenant_id not nullable and add foreign key
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Step 4: Add other columns
        Schema::table('reviews', function (Blueprint $table) {
            // Outscraper review ID for duplicate prevention
            $table->string('outscraper_review_id')->nullable()->after('google_review_id');

            // Additional author info
            $table->text('author_url')->nullable()->after('reviewer_photo_url');

            // Collection timestamp
            $table->timestamp('collected_at')->nullable()->after('review_date');

            // Source tracking
            $table->enum('source', ['google_business', 'outscraper'])->default('outscraper')->after('collected_at');

            // Owner reply fields
            $table->text('owner_reply')->nullable()->after('source');
            $table->timestamp('owner_reply_date')->nullable()->after('owner_reply');
            $table->boolean('replied_via_tabsense')->default(false)->after('owner_reply_date');

            // AI Summary
            $table->text('ai_summary')->nullable()->after('keywords');

            // Quality & filtering
            $table->decimal('quality_score', 3, 2)->default(0.80)->after('ai_summary');
            $table->boolean('is_spam')->default(false)->after('quality_score');
            $table->boolean('is_hidden')->default(false)->after('is_spam');

            // Metadata (likes, author_id, etc.)
            $table->json('metadata')->nullable()->after('is_hidden');

            // Update unique constraint - make google_review_id nullable and add unique per branch
            $table->dropUnique(['google_review_id']);
        });

        // Modify google_review_id to be nullable
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('google_review_id')->nullable()->change();
        });

        // Add new unique constraints and indexes
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['branch_id', 'google_review_id'], 'unique_google_review');
            $table->unique(['branch_id', 'outscraper_review_id'], 'unique_outscraper_review');

            // Additional indexes
            $table->index(['tenant_id', 'branch_id']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Drop new unique constraints
            $table->dropUnique('unique_google_review');
            $table->dropUnique('unique_outscraper_review');
            $table->dropIndex(['tenant_id', 'branch_id']);
            $table->dropIndex(['source']);

            // Drop new columns
            $table->dropForeign(['tenant_id']);
            $table->dropColumn([
                'tenant_id',
                'outscraper_review_id',
                'author_url',
                'collected_at',
                'source',
                'owner_reply',
                'owner_reply_date',
                'replied_via_tabsense',
                'ai_summary',
                'quality_score',
                'is_spam',
                'is_hidden',
                'metadata',
            ]);
        });

        // Restore original unique constraint
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('google_review_id')->nullable(false)->change();
            $table->unique('google_review_id');
        });
    }
};
