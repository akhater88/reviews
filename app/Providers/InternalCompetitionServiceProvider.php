<?php

namespace App\Providers;

use App\Contracts\InternalCompetition\ScoreCalculatorInterface;
use App\Services\InternalCompetition\ArabicNameNormalizer;
use App\Services\InternalCompetition\BenchmarkService;
use App\Services\InternalCompetition\CompetitionService;
use App\Services\InternalCompetition\CustomerSatisfactionScoreService;
use App\Services\InternalCompetition\EmployeeExtractionService;
use App\Services\InternalCompetition\ParticipantService;
use App\Services\InternalCompetition\ResponseTimeScoreService;
use App\Services\InternalCompetition\ScoreCalculationService;
use App\Services\InternalCompetition\WinnerService;
use Illuminate\Support\ServiceProvider;

class InternalCompetitionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Arabic name normalizer
        $this->app->singleton(ArabicNameNormalizer::class, function ($app) {
            return new ArabicNameNormalizer();
        });

        // Register score calculation services
        $this->app->singleton(CustomerSatisfactionScoreService::class, function ($app) {
            return new CustomerSatisfactionScoreService();
        });

        $this->app->singleton(ResponseTimeScoreService::class, function ($app) {
            return new ResponseTimeScoreService();
        });

        // Register employee extraction service
        $this->app->singleton(EmployeeExtractionService::class, function ($app) {
            return new EmployeeExtractionService(
                $app->make(ArabicNameNormalizer::class)
            );
        });

        $this->app->singleton(ScoreCalculationService::class, function ($app) {
            return new ScoreCalculationService(
                $app->make(CustomerSatisfactionScoreService::class),
                $app->make(ResponseTimeScoreService::class),
                $app->make(EmployeeExtractionService::class)
            );
        });

        // Register benchmark service
        $this->app->singleton(BenchmarkService::class, function ($app) {
            return new BenchmarkService();
        });

        // Register winner service
        $this->app->singleton(WinnerService::class, function ($app) {
            return new WinnerService();
        });

        // Register participant service
        $this->app->singleton(ParticipantService::class, function ($app) {
            return new ParticipantService();
        });

        // Register competition service
        $this->app->singleton(CompetitionService::class, function ($app) {
            return new CompetitionService(
                $app->make(ParticipantService::class)
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
