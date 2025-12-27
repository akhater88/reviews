<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Webhooks\PaymentWebhookController;
use App\Models\SuperAdmin;
use Illuminate\Support\Facades\Route;

require __DIR__.'/google-oauth.php';

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
