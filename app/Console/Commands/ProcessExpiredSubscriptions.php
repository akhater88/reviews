<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:process-expired
                            {--dry-run : Preview without processing}';

    protected $description = 'Process expired subscriptions and move to grace period or expired status';

    public function handle(SubscriptionService $service): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('Processing expired subscriptions...');

        // Find subscriptions that have expired
        $subscriptions = Subscription::query()
            ->whereIn('status', [
                SubscriptionStatus::ACTIVE,
                SubscriptionStatus::TRIAL,
                SubscriptionStatus::GRACE_PERIOD,
            ])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $this->info("Found {$subscriptions->count()} subscriptions to process.");

        if ($subscriptions->isEmpty()) {
            return Command::SUCCESS;
        }

        $processed = 0;
        $gracePeriod = 0;
        $expired = 0;

        foreach ($subscriptions as $subscription) {
            if ($dryRun) {
                $this->line(" Would process: {$subscription->tenant->name}");
                continue;
            }

            $service->handleExpired($subscription);

            $subscription->refresh();

            if ($subscription->status === SubscriptionStatus::GRACE_PERIOD) {
                $gracePeriod++;
            } else {
                $expired++;
            }

            $processed++;
        }

        $this->info("Processed: {$processed} | Grace Period: {$gracePeriod} | Expired: {$expired}");

        return Command::SUCCESS;
    }
}
