<?php

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\Controller;
use App\Models\Competition\CompetitionNomination;
use App\Models\Competition\CompetitionParticipant;
use App\Models\Competition\CompetitionPeriod;
use App\Models\Competition\CompetitionScore;
use App\Services\Competition\NominationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitionDashboardController extends Controller
{
    protected NominationService $nominationService;

    public function __construct(NominationService $nominationService)
    {
        $this->nominationService = $nominationService;
    }

    /**
     * Dashboard main page
     */
    public function index(Request $request): View
    {
        /** @var CompetitionParticipant $participant */
        $participant = $request->get('competition_participant');

        $currentPeriod = CompetitionPeriod::current();
        $nomination = null;
        $score = null;
        $rank = null;
        $totalBranches = 0;

        if ($currentPeriod) {
            $nomination = $participant->getNominationForPeriod($currentPeriod->id);

            if ($nomination) {
                $nomination->load('competitionBranch');

                $score = CompetitionScore::where('competition_period_id', $currentPeriod->id)
                    ->where('competition_branch_id', $nomination->competition_branch_id)
                    ->first();

                $rank = $score?->rank_position;
                $totalBranches = $currentPeriod->total_branches;
            }
        }

        // Get leaderboard preview (top 5)
        $leaderboard = $this->getLeaderboardData($currentPeriod, 5);

        // Get nomination history
        $history = $participant->nominations()
            ->with(['competitionBranch', 'period'])
            ->orderByDesc('nominated_at')
            ->limit(5)
            ->get();

        // Get referral stats
        $referralStats = $this->getReferralStats($participant);

        return view('competition.dashboard', compact(
            'participant',
            'currentPeriod',
            'nomination',
            'score',
            'rank',
            'totalBranches',
            'leaderboard',
            'history',
            'referralStats'
        ));
    }

    /**
     * Get current score data (API)
     */
    public function getScore(Request $request): JsonResponse
    {
        /** @var CompetitionParticipant $participant */
        $participant = $request->get('competition_participant');

        $result = $this->nominationService->getParticipantNomination($participant);

        if (!$result) {
            return response()->json([
                'success' => true,
                'has_nomination' => false,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'has_nomination' => true,
            'data' => [
                'branch' => [
                    'name' => $result['branch']->name,
                    'rating' => $result['branch']->google_rating,
                    'reviews_count' => $result['branch']->google_reviews_count,
                    'photo_url' => $result['branch']->photo_url,
                ],
                'score' => $result['score'] ? [
                    'competition_score' => round($result['score']->competition_score, 2),
                    'rank_position' => $result['score']->rank_position,
                    'rating_score' => round($result['score']->rating_score ?? 0, 2),
                    'sentiment_score' => round($result['score']->sentiment_score ?? 0, 2),
                    'response_rate' => round($result['score']->response_rate ?? 0, 1),
                    'volume_score' => round($result['score']->review_volume_score ?? 0, 2),
                    'trend_score' => round($result['score']->trend_score ?? 0, 2),
                    'keyword_score' => round($result['score']->keyword_score ?? 0, 2),
                    'analysis_status' => $result['score']->analysis_status,
                    'last_calculated_at' => $result['score']->last_calculated_at?->toIso8601String(),
                ] : null,
                'rank' => $result['rank'],
                'total_branches' => $result['total_branches'],
                'percentile' => $result['rank'] && $result['total_branches']
                    ? round((1 - ($result['rank'] / $result['total_branches'])) * 100)
                    : null,
            ],
        ]);
    }

    /**
     * Get leaderboard data (API)
     */
    public function getLeaderboard(Request $request): JsonResponse
    {
        $currentPeriod = CompetitionPeriod::current();

        if (!$currentPeriod) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $limit = min($request->input('limit', 10), 50);
        $leaderboard = $this->getLeaderboardData($currentPeriod, $limit);

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
            'period' => [
                'name' => $currentPeriod->name_ar,
                'ends_at' => $currentPeriod->ends_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get nomination history (API)
     */
    public function getHistory(Request $request): JsonResponse
    {
        /** @var CompetitionParticipant $participant */
        $participant = $request->get('competition_participant');

        $history = $participant->nominations()
            ->with(['competitionBranch', 'period'])
            ->orderByDesc('nominated_at')
            ->get()
            ->map(function ($nomination) {
                $score = CompetitionScore::where('competition_period_id', $nomination->competition_period_id)
                    ->where('competition_branch_id', $nomination->competition_branch_id)
                    ->first();

                return [
                    'id' => $nomination->id,
                    'period' => [
                        'name' => $nomination->period->name_ar,
                        'status' => $nomination->period->status,
                    ],
                    'branch' => [
                        'name' => $nomination->competitionBranch->name,
                        'photo_url' => $nomination->competitionBranch->photo_url,
                    ],
                    'nominated_at' => $nomination->nominated_at->toIso8601String(),
                    'rank' => $score?->rank_position,
                    'score' => $score?->competition_score,
                    'is_winner' => $nomination->is_winner,
                    'prize_amount' => $nomination->prize_amount,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Share card page
     */
    public function shareCard(Request $request, int $nominationId): View
    {
        /** @var CompetitionParticipant $participant */
        $participant = $request->get('competition_participant');

        $nomination = CompetitionNomination::where('id', $nominationId)
            ->where('participant_id', $participant->id)
            ->with(['competitionBranch', 'period'])
            ->firstOrFail();

        $score = CompetitionScore::where('competition_period_id', $nomination->competition_period_id)
            ->where('competition_branch_id', $nomination->competition_branch_id)
            ->first();

        return view('competition.share-card', compact('nomination', 'score', 'participant'));
    }

    /**
     * Generate share image
     */
    public function shareImage(Request $request, int $nominationId): View
    {
        /** @var CompetitionParticipant $participant */
        $participant = $request->get('competition_participant');

        $nomination = CompetitionNomination::where('id', $nominationId)
            ->where('participant_id', $participant->id)
            ->with(['competitionBranch', 'period'])
            ->firstOrFail();

        $score = CompetitionScore::where('competition_period_id', $nomination->competition_period_id)
            ->where('competition_branch_id', $nomination->competition_branch_id)
            ->first();

        return view('competition.share-image', compact('nomination', 'score', 'participant'));
    }

    /**
     * Get leaderboard data
     */
    protected function getLeaderboardData(?CompetitionPeriod $period, int $limit = 10): array
    {
        if (!$period) {
            return [];
        }

        return CompetitionScore::where('competition_period_id', $period->id)
            ->whereNotNull('rank_position')
            ->with('competitionBranch')
            ->orderBy('rank_position')
            ->limit($limit)
            ->get()
            ->map(function ($score) {
                return [
                    'rank' => $score->rank_position,
                    'branch' => [
                        'name' => $score->competitionBranch->name,
                        'city' => $score->competitionBranch->city,
                        'photo_url' => $score->competitionBranch->photo_url,
                        'rating' => $score->competitionBranch->google_rating,
                    ],
                    'score' => round($score->competition_score, 2),
                    'nomination_count' => $score->nomination_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get referral statistics
     */
    protected function getReferralStats(CompetitionParticipant $participant): array
    {
        $referrals = CompetitionParticipant::where('referred_by_id', $participant->id)->get();

        return [
            'referral_code' => $participant->referral_code,
            'total_referrals' => $referrals->count(),
            'referral_link' => route('competition.landing', ['ref' => $participant->referral_code]),
        ];
    }
}
