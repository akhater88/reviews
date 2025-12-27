<?php

namespace App\Http\Controllers\Competition;

use App\Enums\CompetitionPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\Competition\CompetitionBranch;
use App\Models\Competition\CompetitionPeriod;
use App\Models\Competition\CompetitionSetting;
use App\Models\Competition\CompetitionWinner;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    /**
     * Display the competition landing page
     */
    public function landing(): View
    {
        // Check if competition is enabled
        if (!CompetitionSetting::isEnabled()) {
            abort(404, 'المسابقة غير متاحة حالياً');
        }

        // Get current period
        $currentPeriod = CompetitionPeriod::current();

        // Get display settings
        $settings = $this->getDisplaySettings();

        // Get stats
        $stats = $this->getPublicStats($currentPeriod);

        // Get participating restaurants (random sample for cloud)
        $restaurants = $this->getParticipatingRestaurants(30);

        // Get previous winners for showcase
        $previousWinners = $this->getPreviousWinners(4);

        // Get score weights for display
        $scoreWeights = CompetitionSetting::getScoreWeights();

        // Get prizes
        $prizes = CompetitionSetting::getPrizes();

        return view('competition.landing', compact(
            'currentPeriod',
            'settings',
            'stats',
            'restaurants',
            'previousWinners',
            'scoreWeights',
            'prizes'
        ));
    }

    /**
     * Get public statistics
     */
    public function stats(): JsonResponse
    {
        $period = CompetitionPeriod::current();

        return response()->json([
            'success' => true,
            'data' => $this->getPublicStats($period),
        ]);
    }

    /**
     * Get participating restaurants for cloud display
     */
    public function participatingRestaurants(): JsonResponse
    {
        $restaurants = $this->getParticipatingRestaurants(50);

        return response()->json([
            'success' => true,
            'data' => $restaurants,
        ]);
    }

    /**
     * Display terms and conditions
     */
    public function terms(): View
    {
        return view('competition.terms');
    }

    /**
     * Display privacy policy
     */
    public function privacy(): View
    {
        return view('competition.privacy');
    }

    /**
     * Display winners page
     */
    public function winners(): View
    {
        $completedPeriods = CompetitionPeriod::where('status', CompetitionPeriodStatus::COMPLETED)
            ->with(['winningBranch', 'winners.participant'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(12);

        return view('competition.winners', compact('completedPeriods'));
    }

    /**
     * Display winners for a specific period
     */
    public function periodWinners(CompetitionPeriod $period): View
    {
        if ($period->status !== CompetitionPeriodStatus::COMPLETED) {
            abort(404);
        }

        $period->load(['winningBranch', 'winners.participant', 'winners.competitionBranch']);

        return view('competition.period-winners', compact('period'));
    }

    /**
     * Get display settings
     */
    protected function getDisplaySettings(): array
    {
        return [
            'hero_title' => CompetitionSetting::get('hero_title', 'مسابقة أفضل مطعم في السعودية'),
            'hero_subtitle' => CompetitionSetting::get('hero_subtitle', 'رشّح مطعمك المفضل واربح إذا فاز!'),
            'cta_button_text' => CompetitionSetting::get('cta_button_text', 'رشّح الآن مجاناً'),
            'show_countdown' => CompetitionSetting::get('show_countdown', true),
            'show_participating_restaurants' => CompetitionSetting::get('show_participating_restaurants', true),
            'show_total_stats' => CompetitionSetting::get('show_total_stats', true),
            'winner_count' => CompetitionSetting::getWinnerCount(),
        ];
    }

    /**
     * Get public statistics
     */
    protected function getPublicStats(?CompetitionPeriod $period): array
    {
        if (!$period) {
            return [
                'total_participants' => 0,
                'total_branches' => 0,
                'total_nominations' => 0,
                'days_remaining' => 0,
                'hours_remaining' => 0,
                'minutes_remaining' => 0,
            ];
        }

        $timeRemaining = $period->time_remaining;

        return [
            'total_participants' => $period->total_participants ?? 0,
            'total_branches' => $period->total_branches ?? 0,
            'total_nominations' => $period->total_nominations ?? 0,
            'days_remaining' => $timeRemaining['days'],
            'hours_remaining' => $timeRemaining['hours'],
            'minutes_remaining' => $timeRemaining['minutes'],
            'period_name' => $period->name_ar ?? $period->name,
            'period_ends_at' => $period->ends_at->toIso8601String(),
        ];
    }

    /**
     * Get participating restaurants (names only, random order)
     */
    protected function getParticipatingRestaurants(int $limit = 30): array
    {
        return CompetitionBranch::active()
            ->eligible()
            ->where('total_nominations', '>', 0)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get previous winners for showcase (names only, privacy protected)
     */
    protected function getPreviousWinners(int $limit = 4): array
    {
        return CompetitionWinner::with('participant')
            ->whereHas('period', fn ($q) => $q->where('status', CompetitionPeriodStatus::COMPLETED))
            ->where('prize_rank', '<=', 3)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($winner) => [
                'name' => $this->maskName($winner->participant->name ?? 'مشارك'),
                'city' => $winner->participant->city ?? 'السعودية',
                'rank' => $winner->prize_rank,
                'prize' => $winner->prize_display,
            ])
            ->toArray();
    }

    /**
     * Mask winner name for privacy (أحمد محمد -> أحمد م.)
     */
    protected function maskName(string $name): string
    {
        $parts = explode(' ', $name);
        if (count($parts) > 1) {
            return $parts[0] . ' ' . mb_substr($parts[1], 0, 1) . '.';
        }

        return $name;
    }
}
