<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Trial tracking (add after is_active if column exists)
            if (! Schema::hasColumn('tenants', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('is_active');
            }

            // Billing info
            if (! Schema::hasColumn('tenants', 'billing_email')) {
                $table->string('billing_email')->nullable()->after('email');
            }
            if (! Schema::hasColumn('tenants', 'billing_address')) {
                $table->text('billing_address')->nullable();
            }
            if (! Schema::hasColumn('tenants', 'tax_number')) {
                $table->string('tax_number')->nullable();
            }
            if (! Schema::hasColumn('tenants', 'country_code')) {
                $table->string('country_code', 2)->default('SA');
            }
            if (! Schema::hasColumn('tenants', 'timezone')) {
                $table->string('timezone')->default('Asia/Riyadh');
            }
            if (! Schema::hasColumn('tenants', 'preferred_currency')) {
                $table->string('preferred_currency', 3)->default('SAR');
            }
        });

        // Add subscription reference in separate schema call to avoid issues
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'current_subscription_id')) {
                $table->unsignedBigInteger('current_subscription_id')->nullable()->after('id');
            }
        });

        // Add indexes
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'trial_ends_at')) {
                $table->index('trial_ends_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $columns = [
                'current_subscription_id',
                'trial_ends_at',
                'billing_email',
                'billing_address',
                'tax_number',
                'country_code',
                'timezone',
                'preferred_currency',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tenants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
