<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->guard('super_admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return redirect()->route('filament.super-admin.auth.login');
        }

        $superAdmin = auth()->guard('super_admin')->user();

        if (!$superAdmin->is_active) {
            auth()->guard('super_admin')->logout();

            return redirect()
                ->route('filament.super-admin.auth.login')
                ->withErrors(['email' => 'حسابك معطل. يرجى التواصل مع الإدارة.']);
        }

        // Update last login
        if (!session()->has('super_admin_login_recorded')) {
            $superAdmin->updateLastLogin();
            session(['super_admin_login_recorded' => true]);
        }

        return $next($request);
    }
}
