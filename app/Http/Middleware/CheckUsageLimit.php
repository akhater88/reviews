<?php

namespace App\Http\Middleware;

use App\Models\UsageSummary;
use App\Services\FeatureGateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUsageLimit
{
    public function __construct(
        protected FeatureGateService $featureGate
    ) {}

    /**
     * Handle an incoming request.
     * Usage: ->middleware('usage_limit:ai_replies_used,max_ai_replies')
     */
    public function handle(Request $request, Closure $next, string $usageKey, string $limitKey): Response
    {
        $tenant = $this->getTenant($request);

        if (! $tenant) {
            return $this->handleNoTenant($request);
        }

        $limit = $this->featureGate->getLimit($tenant, $limitKey);

        // Unlimited
        if ($limit === -1) {
            return $next($request);
        }

        $usage = UsageSummary::getCurrentForTenant($tenant->id);
        $currentUsage = $usage->{$usageKey} ?? 0;

        if ($currentUsage >= $limit) {
            return $this->handleLimitExceeded($request, $limitKey, $currentUsage, $limit);
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

        return null;
    }

    /**
     * Handle no tenant
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
     * Handle limit exceeded
     */
    protected function handleLimitExceeded(Request $request, string $limitKey, int $used, int $limit): Response
    {
        $limitLabels = [
            'max_ai_replies' => 'ردود الذكاء الاصطناعي',
            'max_reviews_sync' => 'مزامنة المراجعات',
            'max_api_calls' => 'طلبات API',
            'max_analysis_runs' => 'تشغيل التحليلات',
        ];

        $limitLabel = $limitLabels[$limitKey] ?? $limitKey;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => "لقد وصلت للحد الأقصى من {$limitLabel} لهذا الشهر",
                'limit_key' => $limitKey,
                'used' => $used,
                'limit' => $limit,
                'upgrade_url' => route('filament.admin.pages.subscription'),
            ], 429);
        }

        return back()
            ->with('error', "لقد وصلت للحد الأقصى من {$limitLabel} ({$used}/{$limit}). يرجى الترقية لزيادة الحد.");
    }
}
