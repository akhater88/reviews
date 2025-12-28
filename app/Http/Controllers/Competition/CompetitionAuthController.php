<?php

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\Controller;
use App\Http\Requests\Competition\RegisterParticipantRequest;
use App\Http\Requests\Competition\SendOtpRequest;
use App\Http\Requests\Competition\VerifyOtpRequest;
use App\Models\Competition\CompetitionParticipant;
use App\Models\Competition\CompetitionPeriod;
use App\Services\Competition\CompetitionOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CompetitionAuthController extends Controller
{
    protected CompetitionOtpService $otpService;

    public function __construct(CompetitionOtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP to phone number
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $phone = $request->getFormattedPhone();

        $result = $this->otpService->sendOtp($phone);

        if ($result['success']) {
            // Store phone in session for verification step
            Session::put('competition_pending_phone', $phone);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'phone_masked' => $result['phone_masked'],
                    'expires_in' => $result['expires_in'],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'data' => [
                'retry_after' => $result['retry_after'] ?? null,
            ],
        ], 422);
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $phone = $request->getFormattedPhone();
        $code = $request->input('code');

        // Verify the phone matches the pending one
        $pendingPhone = Session::get('competition_pending_phone');
        if ($pendingPhone && $pendingPhone !== $phone) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الجوال غير متطابق. يرجى إعادة المحاولة.',
            ], 422);
        }

        $result = $this->otpService->verifyOtp($phone, $code);

        if (!$result['success']) {
            $statusCode = 422;
            if ($result['expired'] ?? false) {
                $statusCode = 410; // Gone
            }
            if ($result['max_attempts'] ?? false) {
                $statusCode = 429; // Too Many Requests
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => [
                    'remaining_attempts' => $result['remaining_attempts'] ?? null,
                    'expired' => $result['expired'] ?? false,
                    'max_attempts' => $result['max_attempts'] ?? false,
                ],
            ], $statusCode);
        }

        // Clear pending phone
        Session::forget('competition_pending_phone');

        // Set authenticated participant in session
        $participant = $result['participant'];
        $this->setAuthenticatedParticipant($participant);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'participant' => [
                    'id' => $participant->id,
                    'phone_masked' => $participant->masked_phone,
                    'name' => $participant->name,
                    'is_registered' => !empty($participant->name),
                ],
                'needs_registration' => $result['needs_registration'],
                'is_new' => $result['is_new'],
            ],
        ]);
    }

    /**
     * Register/update participant profile
     */
    public function register(RegisterParticipantRequest $request): JsonResponse
    {
        $participant = $this->getAuthenticatedParticipant();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى التحقق من رقم الجوال أولاً',
            ], 401);
        }

        // Update participant info
        $participant->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'city' => $request->input('city'),
            'whatsapp_opted_in' => $request->boolean('whatsapp_opted_in', true),
        ]);

        // Handle referral code if provided
        $referralCode = $request->input('referral_code');
        if ($referralCode && !$participant->referred_by_id) {
            $referrer = CompetitionParticipant::where('referral_code', $referralCode)
                ->where('id', '!=', $participant->id)
                ->first();

            if ($referrer) {
                $participant->update(['referred_by_id' => $referrer->id]);
            }
        }

        // Refresh session
        $this->setAuthenticatedParticipant($participant->fresh());

        return response()->json([
            'success' => true,
            'message' => 'تم التسجيل بنجاح! يمكنك الآن ترشيح مطعمك المفضل.',
            'data' => [
                'participant' => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'phone_masked' => $participant->masked_phone,
                    'referral_code' => $participant->referral_code,
                ],
            ],
        ]);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $phone = Session::get('competition_pending_phone');

        if (!$phone) {
            // Try to get from request
            $phone = $request->input('phone');
            if ($phone) {
                $phone = preg_replace('/\D/', '', $phone);
                if (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                if (!str_starts_with($phone, '966')) {
                    $phone = '966' . $phone;
                }
            }
        }

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على رقم الجوال. يرجى إعادة المحاولة.',
            ], 422);
        }

        $result = $this->otpService->resendOtp($phone);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'expires_in' => $result['expires_in'],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'data' => [
                'retry_after' => $result['retry_after'] ?? null,
            ],
        ], 422);
    }

    /**
     * Logout participant
     */
    public function logout(Request $request): JsonResponse
    {
        Session::forget('competition_participant_id');
        Session::forget('competition_pending_phone');
        Session::forget('competition_participant');

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    /**
     * Check authentication status
     */
    public function status(Request $request): JsonResponse
    {
        $participant = $this->getAuthenticatedParticipant();

        if (!$participant) {
            return response()->json([
                'success' => true,
                'data' => [
                    'authenticated' => false,
                    'participant' => null,
                ],
            ]);
        }

        // Check if participant can nominate
        $canNominate = $participant->canNominate();

        // Check current period nomination
        $currentPeriod = CompetitionPeriod::current();
        $hasNominated = $currentPeriod ? $participant->hasNominatedInPeriod($currentPeriod->id) : false;

        return response()->json([
            'success' => true,
            'data' => [
                'authenticated' => true,
                'participant' => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'phone_masked' => $participant->masked_phone,
                    'is_registered' => !empty($participant->name),
                    'referral_code' => $participant->referral_code,
                ],
                'can_nominate' => $canNominate,
                'has_nominated' => $hasNominated,
            ],
        ]);
    }

    /**
     * Set authenticated participant in session
     */
    protected function setAuthenticatedParticipant(CompetitionParticipant $participant): void
    {
        Session::put('competition_participant_id', $participant->id);
        Session::put('competition_participant', [
            'id' => $participant->id,
            'phone' => $participant->phone,
            'name' => $participant->name,
            'verified_at' => $participant->phone_verified_at?->toDateTimeString(),
        ]);
    }

    /**
     * Get authenticated participant from session
     */
    protected function getAuthenticatedParticipant(): ?CompetitionParticipant
    {
        $participantId = Session::get('competition_participant_id');

        if (!$participantId) {
            return null;
        }

        $participant = CompetitionParticipant::find($participantId);

        // Validate participant is still valid
        if (!$participant || $participant->is_blocked || !$participant->phone_verified_at) {
            Session::forget('competition_participant_id');
            Session::forget('competition_participant');

            return null;
        }

        return $participant;
    }
}
