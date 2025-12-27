<?php

namespace App\Http\Middleware;

use App\Services\FeatureGateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureAll
{
    public function __construct(
        protected FeatureGateService $featureGate
    ) {}

    /**
     * Handle an incoming request.
     * Requires ALL specified features (AND logic)
     * Usage: ->middleware('feature_all:ai_reply,google_publish')
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $tenant = $request->user()?->tenant;

        if (! $tenant) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tenant not found'], 403);
            }

            return redirect()->route('login');
        }

        // Check if tenant has ALL required features
        foreach ($features as $feature) {
            if (! $this->featureGate->canAccess($tenant, $feature)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'هذه الميزة غير متاحة في باقتك الحالية',
                        'missing_feature' => $feature,
                        'required_features' => $features,
                    ], 403);
                }

                return redirect()
                    ->route('filament.admin.pages.subscription')
                    ->with('error', 'هذه الميزة غير متاحة في باقتك الحالية');
            }
        }

        return $next($request);
    }
}
