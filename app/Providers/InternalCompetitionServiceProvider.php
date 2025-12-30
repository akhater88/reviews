<?php

namespace App\Providers;

use App\Contracts\InternalCompetition\ScoreCalculatorInterface;
use App\Enums\InternalCompetition\CompetitionMetric;
use App\Services\InternalCompetition\CompetitionService;
use App\Services\InternalCompetition\CustomerSatisfactionScoreService;
use App\Services\InternalCompetition\ParticipantService;
use App\Services\InternalCompetition\ResponseTimeScoreService;
use App\Services\InternalCompetition\ScoreCalculationService;
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

        $this->app->singleton(CustomerSatisfactionScoreService::class, function ($app) {
            return new CustomerSatisfactionScoreService();
        });

        $this->app->singleton(ResponseTimeScoreService::class, function ($app) {
            return new ResponseTimeScoreService();
        });

        $this->app->singleton(ScoreCalculationService::class, function ($app) {
            return new ScoreCalculationService(
                $app->make(CustomerSatisfactionScoreService::class),
                $app->make(ResponseTimeScoreService::class)
            );
        });

        // Bind interface to default implementation (CustomerSatisfaction)
        $this->app->bind(ScoreCalculatorInterface::class, function ($app) {
            return $app->make(CustomerSatisfactionScoreService::class);
        });
    }

    public function boot(): void
    {
        //
    }
}
