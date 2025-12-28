<?php

namespace App\Http\Controllers\Competition;

use App\Exceptions\Competition\AlreadyNominatedException;
use App\Exceptions\Competition\CompetitionClosedException;
use App\Exceptions\Competition\ParticipantBlockedException;
use App\Http\Controllers\Controller;
use App\Models\Competition\CompetitionParticipant;
use App\Services\Competition\GooglePlacesService;
use App\Services\Competition\NominationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompetitionNominationController extends Controller
{
    protected GooglePlacesService $placesService;
    protected NominationService $nominationService;

    public function __construct(
        GooglePlacesService $placesService,
        NominationService $nominationService
    ) {
        $this->placesService = $placesService;
        $this->nominationService = $nominationService;
    }

    /**
     * Search for restaurants via Google Places
     */
    public function searchPlaces(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
            'location' => 'nullable|string', // lat,lng format
        ]);

        $query = $request->input('query');
        $location = $request->input('location');

        $result = $this->placesService->searchRestaurants($query, $location);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في البحث. يرجى المحاولة مرة أخرى.',
                'results' => [],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'results' => $result['results'],
            'count' => $result['count'],
        ]);
    }

    /**
     * Get place details
     */
    public function getPlaceDetails(Request $request, string $placeId): JsonResponse
    {
        $result = $this->placesService->getPlaceDetails($placeId);

        if (!$result['success'] || !$result['place']) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على المطعم',
                'place' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'place' => $result['place'],
        ]);
    }

    /**
     * Submit nomination
     */
    public function nominate(Request $request): JsonResponse
    {
        $request->validate([
            'place_id' => 'required|string|max:255',
            'source' => 'nullable|string|max:50',
        ]);

        /** @var CompetitionParticipant $participant */
        $participant = $request->get('competition_participant');

        try {
            $result = $this->nominationService->nominate(
                $participant,
                $request->input('place_id')
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'nomination' => [
                        'id' => $result['nomination']->id,
                        'nominated_at' => $result['nomination']->nominated_at->toIso8601String(),
                    ],
                    'branch' => [
                        'id' => $result['branch']->id,
                        'name' => $result['branch']->name,
                        'address' => $result['branch']->address,
                        'city' => $result['branch']->city,
                        'rating' => $result['branch']->google_rating,
                        'reviews_count' => $result['branch']->google_reviews_count,
                        'photo_url' => $result['branch']->photo_url,
                    ],
                    'score' => [
                        'competition_score' => $result['score']->competition_score ?? 0,
                        'rank_position' => $result['score']->rank_position,
                        'nomination_count' => $result['score']->nomination_count,
                    ],
                ],
            ]);

        } catch (AlreadyNominatedException $e) {
            $nomination = $e->getNomination();
            $nomination?->load('competitionBranch');

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'already_nominated' => true,
                'data' => $nomination ? [
                    'branch' => [
                        'name' => $nomination->competitionBranch->name,
                        'photo_url' => $nomination->competitionBranch->photo_url,
                    ],
                    'nominated_at' => $nomination->nominated_at->toIso8601String(),
                ] : null,
            ], 409);

        } catch (CompetitionClosedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'competition_closed' => true,
            ], 403);

        } catch (ParticipantBlockedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'participant_blocked' => true,
            ], 403);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Nomination failed', [
                'participant_id' => $participant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الترشيح. يرجى المحاولة مرة أخرى.',
            ], 500);
        }
    }

    /**
     * Get participant's current nomination
     */
    public function myNomination(Request $request): JsonResponse
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
                'nomination' => [
                    'id' => $result['nomination']->id,
                    'nominated_at' => $result['nomination']->nominated_at->toIso8601String(),
                    'is_valid' => $result['nomination']->is_valid,
                ],
                'branch' => [
                    'id' => $result['branch']->id,
                    'name' => $result['branch']->name,
                    'name_ar' => $result['branch']->name_ar,
                    'address' => $result['branch']->address,
                    'city' => $result['branch']->city,
                    'rating' => $result['branch']->google_rating,
                    'reviews_count' => $result['branch']->google_reviews_count,
                    'photo_url' => $result['branch']->photo_url,
                    'google_maps_url' => $result['branch']->google_maps_url,
                ],
                'score' => $result['score'] ? [
                    'competition_score' => $result['score']->competition_score,
                    'rank_position' => $result['score']->rank_position,
                    'nomination_count' => $result['score']->nomination_count,
                    'sentiment_score' => $result['score']->sentiment_score,
                    'response_rate' => $result['score']->response_rate,
                    'analysis_status' => $result['score']->analysis_status,
                ] : null,
                'rank' => $result['rank'],
                'total_branches' => $result['total_branches'],
                'period' => [
                    'name' => $result['period']->name_ar,
                    'ends_at' => $result['period']->ends_at->toIso8601String(),
                    'days_remaining' => $result['period']->days_remaining,
                ],
            ],
        ]);
    }
}
