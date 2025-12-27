<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Mail\SubscriptionExpiringMail;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendExpiryNotifications extends Command
{
    protected $signature = 'subscriptions:notify-expiring
                            {--days=7 : Days before expiry to notify}
                            {--dry-run : Preview without sending}';

    protected $description = 'Send expiry notification emails to subscriptions expiring soon';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Finding subscriptions expiring within {$days} days...");

        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->whereIn('status', [
                SubscriptionStatus::ACTIVE,
                SubscriptionStatus::TRIAL,
            ])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>', now())
            ->where(function ($query) use ($days) {
                // Don't notify if already notified for this period
                $query->whereNull('last_expiry_notification_at')
                    ->orWhere('last_expiry_notification_at', '<', now()->subDays($days));
            })
            ->get();

        $this->info("Found {$subscriptions->count()} subscriptions to notify.");

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions need notification.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($subscriptions->count());
        $sent = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            $tenant = $subscription->tenant;
            $email = $tenant->billing_email ?? $tenant->email;

            if ($dryRun) {
                $this->line(" Would send to: {$email} ({$tenant->name})");
            } else {
                try {
                    Mail::to($email)->send(new SubscriptionExpiringMail($subscription));

                    $subscription->update([
                        'last_expiry_notification_at' => now(),
                    ]);

                    $sent++;
                } catch (\Exception $e) {
                    $failed++;
                    $this->error(" Failed: {$email} - {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if (!$dryRun) {
            $this->info("Sent: {$sent} | Failed: {$failed}");
        }

        return Command::SUCCESS;
    }
}
