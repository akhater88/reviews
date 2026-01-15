<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use App\Horizon\SafeRedisMasterSupervisorRepository;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        // Override the MasterSupervisorRepository to handle corrupted Redis data
        $this->app->singleton(MasterSupervisorRepository::class, SafeRedisMasterSupervisorRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            // Allow access in local environment
            if (app()->environment('local')) {
                return true;
            }

            // Only allow super admins to access Horizon
            return Auth::guard('super_admin')->check();
        });
    }
}
