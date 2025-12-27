<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('price_at_renewal', 10, 2)->nullable()->after('amount_paid');
            $table->decimal('proration_credit', 10, 2)->default(0)->after('price_at_renewal');
            $table->timestamp('next_billing_date')->nullable()->after('cancelled_at');
            $table->timestamp('renewed_at')->nullable()->after('next_billing_date');
            $table->timestamp('last_expiry_notification_at')->nullable()->after('renewed_at');
            $table->boolean('cancel_at_period_end')->default(false)->after('auto_renew');
            $table->foreignId('scheduled_plan_id')->nullable()->after('cancel_at_period_end')
                ->constrained('plans')->nullOnDelete();
            $table->timestamp('scheduled_change_date')->nullable()->after('scheduled_plan_id');
            $table->text('notes')->nullable()->after('scheduled_change_date');
        });

        // Add payment_reference to invoices if not exists
        if (!Schema::hasColumn('invoices', 'payment_reference')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('payment_reference')->nullable()->after('paid_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['scheduled_plan_id']);
            $table->dropColumn([
                'price_at_renewal',
                'proration_credit',
                'next_billing_date',
                'renewed_at',
                'last_expiry_notification_at',
                'cancel_at_period_end',
                'scheduled_plan_id',
                'scheduled_change_date',
                'notes',
            ]);
        });

        if (Schema::hasColumn('invoices', 'payment_reference')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('payment_reference');
            });
        }
    }
};
