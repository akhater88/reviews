<?php

namespace App\Providers;

use App\Services\Competition\CompetitionOtpService;
use App\Services\Infobip\InfobipService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureCompetitionRateLimiting();
    }

    /**
     * Configure rate limiting for competition routes.
     */
    protected function configureCompetitionRateLimiting(): void
    {
        // Rate limiter for nomination - uses participant session ID for authenticated users
        RateLimiter::for('competition-nominate', function (Request $request) {
            $participantId = $request->session()->get('competition_participant_id');

            if ($participantId) {
                // Authenticated participant: 10 attempts per minute per participant
                return Limit::perMinute(10)->by('participant:' . $participantId);
            }

            // Fallback to IP-based limiting for unauthenticated requests
            return Limit::perMinute(5)->by($request->ip());
        });

        // Rate limiter for places search - more generous for authenticated users
        RateLimiter::for('competition-places-search', function (Request $request) {
            $participantId = $request->session()->get('competition_participant_id');

            if ($participantId) {
                return Limit::perMinute(60)->by('participant:' . $participantId);
            }

            return Limit::perMinute(30)->by($request->ip());
        });

        // Rate limiter for place details
        RateLimiter::for('competition-place-details', function (Request $request) {
            $participantId = $request->session()->get('competition_participant_id');

            if ($participantId) {
                return Limit::perMinute(40)->by('participant:' . $participantId);
            }

            return Limit::perMinute(20)->by($request->ip());
        });

        // Rate limiter for OTP sending - stricter to prevent abuse
        RateLimiter::for('competition-otp', function (Request $request) {
            $phone = $request->input('phone', '');
            // Key by phone number to prevent abuse of specific numbers
            return Limit::perMinute(5)->by('otp:' . $phone . ':' . $request->ip());
        });

        // Rate limiter for OTP verification
        RateLimiter::for('competition-verify-otp', function (Request $request) {
            $phone = $request->input('phone', '');
            return Limit::perMinute(10)->by('verify:' . $phone . ':' . $request->ip());
        });

        // Rate limiter for OTP resend
        RateLimiter::for('competition-resend-otp', function (Request $request) {
            $phone = $request->session()->get('competition_pending_phone', $request->input('phone', ''));
            return Limit::perMinute(3)->by('resend:' . $phone . ':' . $request->ip());
        });
    }
}
