<?php

use App\Http\Controllers\Api\FreeReportController;
use App\Http\Controllers\FreeReportPageController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Competition\CompetitionAuthController;
use App\Http\Controllers\Competition\CompetitionController;
use App\Http\Controllers\Competition\CompetitionDashboardController;
use App\Http\Controllers\Competition\CompetitionNominationController;
use App\Http\Controllers\Competition\PrizeClaimController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Webhooks\PaymentWebhookController;
use App\Models\SuperAdmin;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

require __DIR__.'/google-oauth.php';

// TABsense Marketing Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/get-started', [LandingController::class, 'getStarted'])->name('get-started');

// Tenant-specific login route
Route::get('/login/{tenant:slug}', function (Tenant $tenant) {
    session(['login_tenant_id' => $tenant->id]);
    return redirect('/restaurant-owners/login');
})->name('tenant.login');

// Competition Public Routes
Route::prefix('competition')->name('competition.')->group(function () {
    // Landing Page
    Route::get('/', [CompetitionController::class, 'landing'])->name('landing');

    // Public API endpoints
    Route::get('/stats', [CompetitionController::class, 'stats'])->name('stats');
    Route::get('/restaurants', [CompetitionController::class, 'participatingRestaurants'])->name('restaurants');

    // Terms & Privacy
    Route::get('/terms', [CompetitionController::class, 'terms'])->name('terms');
    Route::get('/privacy', [CompetitionController::class, 'privacy'])->name('privacy');

    // Winners Page (public)
    Route::get('/winners', [CompetitionController::class, 'winners'])->name('winners');
    Route::get('/winners/{period:slug}', [CompetitionController::class, 'periodWinners'])->name('winners.period');

    // Prize claim routes (public with code verification)
    Route::get('/claim/{code}', [PrizeClaimController::class, 'show'])
        ->name('claim');
    Route::post('/claim/{code}', [PrizeClaimController::class, 'submit'])
        ->name('claim.submit');
    Route::get('/claim/{code}/success', [PrizeClaimController::class, 'success'])
        ->name('claim.success');

    // Authentication Routes
    Route::post('/send-otp', [CompetitionAuthController::class, 'sendOtp'])
        ->name('send-otp')
        ->middleware('throttle:competition-otp');

    Route::post('/verify-otp', [CompetitionAuthController::class, 'verifyOtp'])
        ->name('verify-otp')
        ->middleware('throttle:competition-verify-otp');

    Route::post('/register', [CompetitionAuthController::class, 'register'])
        ->name('register');

    Route::post('/resend-otp', [CompetitionAuthController::class, 'resendOtp'])
        ->name('resend-otp')
        ->middleware('throttle:competition-resend-otp');

    Route::post('/logout', [CompetitionAuthController::class, 'logout'])
        ->name('logout');

    // Check auth status (for frontend)
    Route::get('/auth-status', [CompetitionAuthController::class, 'status'])
        ->name('auth-status');

    // Protected routes (require verified participant)
    Route::middleware('competition.auth')->group(function () {
        // Google Places Search
        Route::get('/places/search', [CompetitionNominationController::class, 'searchPlaces'])
            ->name('places.search')
            ->middleware('throttle:competition-places-search');

        Route::get('/places/{placeId}', [CompetitionNominationController::class, 'getPlaceDetails'])
            ->name('places.details')
            ->middleware('throttle:competition-place-details');

        // Nomination
        Route::post('/nominate', [CompetitionNominationController::class, 'nominate'])
            ->name('nominate')
            ->middleware('throttle:competition-nominate');

        Route::get('/my-nomination', [CompetitionNominationController::class, 'myNomination'])
            ->name('my-nomination');

        // Dashboard
        Route::get('/dashboard', [CompetitionDashboardController::class, 'index'])
            ->name('dashboard');

        // Dashboard API endpoints
        Route::get('/dashboard/score', [CompetitionDashboardController::class, 'getScore'])
            ->name('dashboard.score');

        Route::get('/dashboard/leaderboard', [CompetitionDashboardController::class, 'getLeaderboard'])
            ->name('dashboard.leaderboard');

        Route::get('/dashboard/history', [CompetitionDashboardController::class, 'getHistory'])
            ->name('dashboard.history');

        // Share
        Route::get('/share/{nominationId}', [CompetitionDashboardController::class, 'shareCard'])
            ->name('share.card');

        Route::get('/share/{nominationId}/image', [CompetitionDashboardController::class, 'shareImage'])
            ->name('share.image');
    });
});

// Payment Webhooks (no CSRF, no auth)
Route::post('/webhooks/payment/{gateway}', [PaymentWebhookController::class, 'handle'])
    ->name('webhooks.payment')
    ->withoutMiddleware(['web', \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Checkout routes (requires auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout/{invoice}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{invoice}', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/payment/success/{invoice}', [CheckoutController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel/{invoice}', [CheckoutController::class, 'cancel'])->name('payment.cancel');
});

// Super Admin return from impersonation
Route::get('/super-admin/return', function () {
    $superAdminId = session('impersonating_from');

    if (!$superAdminId) {
        return redirect('/admin');
    }

    // Logout from tenant
    auth()->guard('web')->logout();

    // Clear impersonation session
    session()->forget('impersonating_from');

    // Login back as super admin
    $superAdmin = SuperAdmin::find($superAdminId);
    if ($superAdmin) {
        auth()->guard('super_admin')->login($superAdmin);
    }

    return redirect('/super-admin');
})->name('super-admin.return');

// Free Report API Routes
Route::prefix('api/free-report')->name('free-report.')->group(function () {
    // OTP verification
    Route::post('/request-otp', [FreeReportController::class, 'requestOtp'])
        ->name('request-otp')
        ->middleware('throttle:free-report-otp');

    Route::post('/verify-otp', [FreeReportController::class, 'verifyOtp'])
        ->name('verify-otp')
        ->middleware('throttle:free-report-verify');

    // Report creation
    Route::post('/create', [FreeReportController::class, 'createReport'])
        ->name('create')
        ->middleware('throttle:free-report-create');

    // Report status
    Route::get('/status', [FreeReportController::class, 'getStatus'])
        ->name('status');

    // Resend magic link
    Route::post('/resend-link', [FreeReportController::class, 'resendMagicLink'])
        ->name('resend-link')
        ->middleware('throttle:free-report-resend');

    // View report by magic token
    Route::get('/view/{token}', [FreeReportController::class, 'getReportByToken'])
        ->name('view');
});

// Free Report Public Pages (No Auth Required)
Route::prefix('free-report')->name('free-report.page.')->group(function () {
    // Main report page
    Route::get('{token}', [FreeReportPageController::class, 'show'])
        ->name('show');

    // Request access (for returning users)
    Route::get('access', [FreeReportPageController::class, 'accessForm'])
        ->name('access');

    Route::post('access', [FreeReportPageController::class, 'requestAccess'])
        ->name('request-access');
});
