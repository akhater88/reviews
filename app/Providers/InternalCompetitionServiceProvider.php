<?php

namespace App\Providers;

use App\Services\InternalCompetition\CompetitionService;
use App\Services\InternalCompetition\ParticipantService;
use Illuminate\Support\ServiceProvider;

class InternalCompetitionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ParticipantService::class, function ($app) {
            return new ParticipantService();
        });

        $this->app->singleton(CompetitionService::class, function ($app) {
            return new CompetitionService(
                $app->make(ParticipantService::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
