<?php

namespace App\Services\Competition;

use App\Exceptions\Competition\AlreadyNominatedException;
use App\Exceptions\Competition\CompetitionClosedException;
use App\Exceptions\Competition\ParticipantBlockedException;
use App\Jobs\Competition\SyncCompetitionBranchReviewsJob;
use App\Models\Competition\CompetitionBranch;
use App\Models\Competition\CompetitionNomination;
use App\Models\Competition\CompetitionParticipant;
use App\Models\Competition\CompetitionPeriod;
use App\Models\Competition\CompetitionScore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NominationService
{
    protected GooglePlacesService $placesService;

    public function __construct(GooglePlacesService $placesService)
    {
        $this->placesService = $placesService;
    }

    /**
     * Submit a nomination
     */
    public function nominate(
        CompetitionParticipant $participant,
        string $placeId
    ): array {
        // Get current period
        $period = CompetitionPeriod::current();

        if (!$period || !$period->canAcceptNominations()) {
            throw new CompetitionClosedException('المسابقة غير متاحة حالياً');
        }

        // Check participant can nominate
        if (!$participant->canNominate()) {
            throw new ParticipantBlockedException('لا يمكنك المشاركة في المسابقة');
        }

        // Check if already nominated this period
        if ($participant->hasNominatedInPeriod($period->id)) {
            $existingNomination = $participant->getNominationForPeriod($period->id);
            throw new AlreadyNominatedException(
                'لقد قمت بالترشيح مسبقاً هذا الشهر',
                $existingNomination
            );
        }

        // Get place details from Google
        $placeResult = $this->placesService->getPlaceDetails($placeId);

        if (!$placeResult['success'] || !$placeResult['place']) {
            throw new \InvalidArgumentException('لم يتم العثور على المطعم');
        }

        $placeData = $placeResult['place'];

        // Validate minimum requirements
        $this->validateBranchRequirements($placeData, $period);

        // Create nomination in transaction
        return DB::transaction(function () use ($participant, $period, $placeId, $placeData) {
            // Find or create competition branch (UNIQUE by google_place_id)
            $branch = $this->findOrCreateCompetitionBranch($placeId, $placeData, $participant);

            // Create nomination
            $nomination = CompetitionNomination::create([
                'competition_period_id' => $period->id,
                'participant_id' => $participant->id,
                'competition_branch_id' => $branch->id,
                'nominated_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'device_type' => $this->detectDeviceType(),
                'source' => request()->input('source', 'direct'),
            ]);

            // Create or update score record
            $score = $this->createOrUpdateScore($period, $branch);

            // Update counts
            $this->updateCounts($period, $branch);

            // Dispatch review sync job
            $this->dispatchReviewSync($branch);

            Log::info('Competition nomination created', [
                'nomination_id' => $nomination->id,
                'participant_id' => $participant->id,
                'branch_id' => $branch->id,
                'period_id' => $period->id,
            ]);

            return [
                'success' => true,
                'nomination' => $nomination,
                'branch' => $branch,
                'score' => $score,
                'message' => 'تم تسجيل ترشيحك بنجاح!',
            ];
        });
    }

    /**
     * Find or create competition branch by google_place_id
     */
    protected function findOrCreateCompetitionBranch(
        string $placeId,
        array $placeData,
        CompetitionParticipant $participant
    ): CompetitionBranch {
        $branch = CompetitionBranch::where('google_place_id', $placeId)->first();

        if ($branch) {
            // Update with latest data from Google
            $branch->update([
                'google_rating' => $placeData['rating'],
                'google_reviews_count' => $placeData['reviews_count'],
                'photo_url' => $placeData['photo_url'],
            ]);

            return $branch;
        }

        // Create new competition branch
        return CompetitionBranch::create([
            'google_place_id' => $placeId,
            'name' => $placeData['name'],
            'name_ar' => $placeData['name_ar'] ?? $placeData['name'],
            'address' => $placeData['address'],
            'city' => $placeData['city'],
            'country' => $placeData['country'] ?? 'Saudi Arabia',
            'google_rating' => $placeData['rating'],
            'google_reviews_count' => $placeData['reviews_count'],
            'latitude' => $placeData['latitude'],
            'longitude' => $placeData['longitude'],
            'photo_url' => $placeData['photo_url'],
            'photos' => $placeData['photos'] ?? [],
            'phone_number' => $placeData['phone'],
            'website' => $placeData['website'],
            'opening_hours' => $placeData['opening_hours'],
            'types' => $placeData['types'],
            'first_nominated_at' => now(),
            'first_nominated_by' => $participant->id,
            'total_nominations' => 0,
            'sync_status' => 'pending',
            'is_active' => true,
            'is_eligible' => true,
        ]);
    }

    /**
     * Create or update competition score
     */
    protected function createOrUpdateScore(
        CompetitionPeriod $period,
        CompetitionBranch $branch
    ): CompetitionScore {
        return CompetitionScore::updateOrCreate(
            [
                'competition_period_id' => $period->id,
                'competition_branch_id' => $branch->id,
            ],
            [
                'overall_rating' => $branch->google_rating ?? 0,
                'total_reviews' => $branch->google_reviews_count ?? 0,
                'analysis_status' => 'pending',
            ]
        );
    }

    /**
     * Update nomination counts
     */
    protected function updateCounts(CompetitionPeriod $period, CompetitionBranch $branch): void
    {
        // Update branch total nominations
        $branch->increment('total_nominations');

        // Update score nomination count
        CompetitionScore::where('competition_period_id', $period->id)
            ->where('competition_branch_id', $branch->id)
            ->increment('nomination_count');

        // Update period stats
        $period->recalculateStats();
    }

    /**
     * Dispatch job to sync branch reviews
     */
    protected function dispatchReviewSync(CompetitionBranch $branch): void
    {
        // Only sync if not recently synced
        if ($branch->reviews_last_synced_at && $branch->reviews_last_synced_at->diffInHours(now()) < 24) {
            return;
        }

        dispatch(new SyncCompetitionBranchReviewsJob($branch))
            ->onQueue('competition')
            ->delay(now()->addSeconds(30)); // Delay to avoid API rate limits
    }

    /**
     * Validate branch meets minimum requirements
     */
    protected function validateBranchRequirements(array $placeData, CompetitionPeriod $period): void
    {
        $minReviews = $period->min_reviews_required ?? 10;

        if (($placeData['reviews_count'] ?? 0) < $minReviews) {
            throw new \InvalidArgumentException(
                "المطعم يحتاج {$minReviews} تقييمات على الأقل للمشاركة في المسابقة"
            );
        }

        if (($placeData['rating'] ?? 0) < 1) {
            throw new \InvalidArgumentException('المطعم لا يملك تقييم كافي للمشاركة');
        }

        if ($placeData['business_status'] !== 'OPERATIONAL') {
            throw new \InvalidArgumentException('المطعم غير متاح حالياً');
        }
    }

    /**
     * Get participant's nomination for current period
     */
    public function getParticipantNomination(CompetitionParticipant $participant): ?array
    {
        $period = CompetitionPeriod::current();

        if (!$period) {
            return null;
        }

        $nomination = $participant->getNominationForPeriod($period->id);

        if (!$nomination) {
            return null;
        }

        $nomination->load(['competitionBranch', 'period']);

        $score = CompetitionScore::where('competition_period_id', $period->id)
            ->where('competition_branch_id', $nomination->competition_branch_id)
            ->first();

        return [
            'nomination' => $nomination,
            'branch' => $nomination->competitionBranch,
            'period' => $period,
            'score' => $score,
            'rank' => $score?->rank_position,
            'total_branches' => $period->total_branches,
        ];
    }

    /**
     * Detect device type
     */
    protected function detectDeviceType(): string
    {
        $userAgent = strtolower(request()->userAgent() ?? '');

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')) {
            return 'mobile';
        }

        if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }

        return 'desktop';
    }
}
