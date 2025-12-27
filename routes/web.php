<?php

use App\Models\SuperAdmin;
use Illuminate\Support\Facades\Route;

require __DIR__.'/google-oauth.php';

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
