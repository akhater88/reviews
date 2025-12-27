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
