<?php

use App\Jobs\SyncAllBranchesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Horizon Metrics
|--------------------------------------------------------------------------
|
| Capture Horizon metrics every 5 minutes for the dashboard graphs.
|
*/

Schedule::command('horizon:snapshot')
    ->everyFiveMinutes()
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| Review Sync Scheduler
|--------------------------------------------------------------------------
|
| Schedule automated review syncing for branches.
| Manual owned branches: Daily at 2 AM
| Competitor branches: Weekly on Sunday at 3 AM
|
*/

// Sync manual owned branches daily at 2 AM
Schedule::job(new SyncAllBranchesJob(sourceFilter: 'manual', typeFilter: 'owned'))
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->name('sync-manual-owned-branches')
    ->onOneServer();

// Sync competitor branches weekly on Sunday at 3 AM
Schedule::job(new SyncAllBranchesJob(sourceFilter: 'manual', typeFilter: 'competitor'))
    ->weeklyOn(0, '03:00')
    ->withoutOverlapping()
    ->name('sync-competitor-branches')
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| Subscription Management Scheduler
|--------------------------------------------------------------------------
|
| Run subscription-related scheduled tasks:
| - Expiry notifications: Daily at 9 AM (7, 3, and 1 day reminders)
| - Process expired subscriptions: Hourly
|
*/

// Send expiry notifications (7 days before)
Schedule::command('subscriptions:notify-expiring --days=7')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->name('subscription-notify-7-days')
    ->onOneServer();

// Send expiry notifications (3 days before)
Schedule::command('subscriptions:notify-expiring --days=3')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->name('subscription-notify-3-days')
    ->onOneServer();

// Send expiry notifications (1 day before)
Schedule::command('subscriptions:notify-expiring --days=1')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->name('subscription-notify-1-day')
    ->onOneServer();

// Process expired subscriptions every hour
Schedule::command('subscriptions:process-expired')
    ->hourly()
    ->withoutOverlapping()
    ->name('process-expired-subscriptions')
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| Competition Management Scheduler
|--------------------------------------------------------------------------
|
| Run competition-related scheduled tasks:
| - Sync reviews: Every 4 hours for stale branches
| - Update rankings: Hourly
| - Full score recalculation: Daily at 3 AM
| - Period transitions: Daily
|
*/

// Competition: Sync reviews every 4 hours for branches not synced in 6 hours
Schedule::command('competition:sync-reviews --stale-hours=6')
    ->everyFourHours()
    ->withoutOverlapping()
    ->name('competition-sync-reviews')
    ->onOneServer();

// Competition: Update rankings hourly
Schedule::command('competition:update-rankings')
    ->hourly()
    ->withoutOverlapping()
    ->name('competition-update-rankings')
    ->onOneServer();

// Competition: Full score recalculation daily at 3 AM
Schedule::command('competition:process-scores')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->name('competition-process-scores')
    ->onOneServer();

// Competition: Check for periods to close and select winners daily at midnight
Schedule::call(function () {
    $expiredPeriods = \App\Models\Competition\CompetitionPeriod::where('status', 'active')
        ->where('ends_at', '<', now())
        ->get();

    foreach ($expiredPeriods as $period) {
        $period->update(['status' => \App\Enums\CompetitionPeriodStatus::COMPLETED]);

        // Only dispatch winner selection if not already selected
        if (!$period->winners_selected) {
            dispatch(new \App\Jobs\Competition\SelectWinnersJob($period))
                ->onQueue('competition');
        }
    }

    // Activate upcoming periods
    \App\Models\Competition\CompetitionPeriod::where('status', 'draft')
        ->where('starts_at', '<=', now())
        ->where('ends_at', '>', now())
        ->update(['status' => \App\Enums\CompetitionPeriodStatus::ACTIVE]);
})
    ->dailyAt('00:05')
    ->name('competition-period-transitions')
    ->onOneServer();

// Competition: Send claim reminders weekly on Mondays at 10 AM
Schedule::job(new \App\Jobs\Competition\SendClaimRemindersJob)
    ->weeklyOn(1, '10:00')
    ->withoutOverlapping()
    ->name('competition-claim-reminders')
    ->onOneServer();
