<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     * Ensures tenant has an active subscription.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->getTenant($request);

        if (! $tenant) {
            return $this->handleNoTenant($request);
        }

        if (! $tenant->hasActiveSubscription()) {
            return $this->handleInactiveSubscription($request, $tenant);
        }

        return $next($request);
    }

    /**
     * Get tenant from request
     */
    protected function getTenant(Request $request)
    {
        // Get from authenticated user
        $user = $request->user();

        if ($user && $user->tenant) {
            return $user->tenant;
        }

        // Get from session/context if using multi-tenancy package
        if (function_exists('tenant')) {
            return tenant();
        }

        return null;
    }

    /**
     * Handle case when no tenant found
     */
    protected function handleNoTenant(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found',
            ], 403);
        }

        return redirect()->route('login');
    }

    /**
     * Handle inactive subscription
     */
    protected function handleInactiveSubscription(Request $request, $tenant): Response
    {
        $status = $tenant->currentSubscription?->status;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'الاشتراك غير نشط',
                'subscription_status' => $status?->value,
                'subscription_label' => $status?->label(),
            ], 403);
        }

        // Redirect to subscription page
        return redirect()
            ->route('filament.admin.pages.subscription')
            ->with('error', 'يجب تجديد الاشتراك للوصول لهذه الصفحة');
    }
}
