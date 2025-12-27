<?php

namespace App\Http\Middleware;

use App\Services\FeatureGateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    public function __construct(
        protected FeatureGateService $featureGate
    ) {}

    /**
     * Handle an incoming request.
     * Usage: ->middleware('feature:monthly_rankings')
     * Usage: ->middleware('feature:ai_reply,competitor_analysis') // Any of these
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $tenant = $this->getTenant($request);

        if (! $tenant) {
            return $this->handleNoTenant($request);
        }

        // Check if tenant has any of the required features
        $hasAccess = false;
        foreach ($features as $feature) {
            if ($this->featureGate->canAccess($tenant, $feature)) {
                $hasAccess = true;
                break;
            }
        }

        if (! $hasAccess) {
            return $this->handleNoAccess($request, $features);
        }

        return $next($request);
    }

    /**
     * Get tenant from request
     */
    protected function getTenant(Request $request)
    {
        $user = $request->user();

        if ($user && $user->tenant) {
            return $user->tenant;
        }

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
     * Handle no access to feature
     */
    protected function handleNoAccess(Request $request, array $features): Response
    {
        $featureNames = implode(', ', $features);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الميزة غير متاحة في باقتك الحالية',
                'required_features' => $features,
                'upgrade_url' => route('filament.admin.pages.subscription'),
            ], 403);
        }

        return redirect()
            ->route('filament.admin.pages.subscription')
            ->with('error', 'هذه الميزة غير متاحة في باقتك الحالية. يرجى الترقية للوصول.')
            ->with('required_features', $features);
    }
}
