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
        if (!Schema::hasColumn('reviews', 'tenant_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            });
        }

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
        });

        // Add foreign key if not exists
        $foreignKeys = $this->getForeignKeys('reviews');
        if (!in_array('reviews_tenant_id_foreign', $foreignKeys)) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // Step 4: Add other columns (check each one)
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'outscraper_review_id')) {
                $table->string('outscraper_review_id')->nullable()->after('google_review_id');
            }

            if (!Schema::hasColumn('reviews', 'author_url')) {
                $table->text('author_url')->nullable()->after('reviewer_photo_url');
            }

            if (!Schema::hasColumn('reviews', 'collected_at')) {
                $table->timestamp('collected_at')->nullable()->after('review_date');
            }

            if (!Schema::hasColumn('reviews', 'source')) {
                $table->enum('source', ['google_business', 'outscraper'])->default('outscraper')->after('collected_at');
            }

            if (!Schema::hasColumn('reviews', 'owner_reply')) {
                $table->text('owner_reply')->nullable()->after('source');
            }

            if (!Schema::hasColumn('reviews', 'owner_reply_date')) {
                $table->timestamp('owner_reply_date')->nullable()->after('owner_reply');
            }

            if (!Schema::hasColumn('reviews', 'replied_via_tabsense')) {
                $table->boolean('replied_via_tabsense')->default(false)->after('owner_reply_date');
            }

            if (!Schema::hasColumn('reviews', 'ai_summary')) {
                $table->text('ai_summary')->nullable()->after('keywords');
            }

            if (!Schema::hasColumn('reviews', 'quality_score')) {
                $table->decimal('quality_score', 3, 2)->default(0.80)->after('ai_summary');
            }

            if (!Schema::hasColumn('reviews', 'is_spam')) {
                $table->boolean('is_spam')->default(false)->after('quality_score');
            }

            if (!Schema::hasColumn('reviews', 'is_hidden')) {
                $table->boolean('is_hidden')->default(false)->after('is_spam');
            }

            if (!Schema::hasColumn('reviews', 'metadata')) {
                $table->json('metadata')->nullable()->after('is_hidden');
            }
        });

        // Drop old unique constraint if exists
        $indexes = $this->getIndexes('reviews');
        if (in_array('reviews_google_review_id_unique', $indexes)) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropUnique(['google_review_id']);
            });
        }

        // Modify google_review_id to be nullable
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('google_review_id')->nullable()->change();
        });

        // Add new unique constraints and indexes (check each one)
        Schema::table('reviews', function (Blueprint $table) use ($indexes) {
            if (!in_array('unique_google_review', $indexes)) {
                $table->unique(['branch_id', 'google_review_id'], 'unique_google_review');
            }

            if (!in_array('unique_outscraper_review', $indexes)) {
                $table->unique(['branch_id', 'outscraper_review_id'], 'unique_outscraper_review');
            }

            if (!in_array('reviews_tenant_id_branch_id_index', $indexes)) {
                $table->index(['tenant_id', 'branch_id']);
            }

            if (!in_array('reviews_source_index', $indexes)) {
                $table->index('source');
            }
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

    /**
     * Get list of foreign keys for a table
     */
    private function getForeignKeys(string $table): array
    {
        $foreignKeys = [];
        $results = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$table]);

        foreach ($results as $result) {
            $foreignKeys[] = $result->CONSTRAINT_NAME;
        }

        return $foreignKeys;
    }

    /**
     * Get list of indexes for a table
     */
    private function getIndexes(string $table): array
    {
        $indexes = [];
        $results = DB::select("SHOW INDEX FROM {$table}");

        foreach ($results as $result) {
            $indexes[] = $result->Key_name;
        }

        return array_unique($indexes);
    }
};
