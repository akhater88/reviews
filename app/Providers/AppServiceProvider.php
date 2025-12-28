<?php

namespace App\Providers;

use App\Services\Competition\CompetitionOtpService;
use App\Services\Infobip\InfobipService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(InfobipService::class, function ($app) {
            return new InfobipService();
        });

        $this->app->singleton(CompetitionOtpService::class, function ($app) {
            return new CompetitionOtpService(
                $app->make(InfobipService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
