<?php

namespace App\Services\Competition;

use App\Models\Competition\CompetitionParticipant;
use App\Services\Infobip\InfobipService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompetitionOtpService
{
    protected InfobipService $infobipService;

    // OTP settings
    protected const OTP_LENGTH = 6;
    protected const OTP_EXPIRY_MINUTES = 10;
    protected const MAX_ATTEMPTS = 5;
    protected const COOLDOWN_MINUTES = 1;
    protected const MAX_DAILY_SENDS = 10;

    public function __construct(InfobipService $infobipService)
    {
        $this->infobipService = $infobipService;
    }

    /**
     * Generate and send OTP to phone number
     */
    public function sendOtp(string $phone): array
    {
        // Check cooldown
        if ($this->isInCooldown($phone)) {
            $remainingSeconds = $this->getCooldownRemaining($phone);

            return [
                'success' => false,
                'message' => "يرجى الانتظار {$remainingSeconds} ثانية قبل إعادة المحاولة",
                'retry_after' => $remainingSeconds,
            ];
        }

        // Check daily limit
        if ($this->hasExceededDailyLimit($phone)) {
            return [
                'success' => false,
                'message' => 'تم تجاوز الحد اليومي للرسائل. يرجى المحاولة غداً.',
            ];
        }

        // Check if participant is blocked
        $participant = CompetitionParticipant::where('phone', $phone)->first();
        if ($participant && $participant->is_blocked) {
            return [
                'success' => false,
                'message' => 'هذا الرقم محظور من المشاركة.',
            ];
        }

        // Generate OTP
        $otp = $this->generateOtp();

        // Store OTP in cache
        $this->storeOtp($phone, $otp);

        // Send via WhatsApp
        $sent = $this->sendWhatsAppOtp($phone, $otp);

        if (!$sent) {
            return [
                'success' => false,
                'message' => 'فشل في إرسال رمز التحقق. يرجى المحاولة مرة أخرى.',
            ];
        }

        // Set cooldown
        $this->setCooldown($phone);

        // Increment daily counter
        $this->incrementDailyCounter($phone);

        return [
            'success' => true,
            'message' => 'تم إرسال رمز التحقق إلى واتساب',
            'expires_in' => self::OTP_EXPIRY_MINUTES * 60,
            'phone_masked' => $this->maskPhone($phone),
        ];
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(string $phone, string $code): array
    {
        $cacheKey = $this->getOtpCacheKey($phone);
        $attemptsKey = $this->getAttemptsCacheKey($phone);

        // Check if OTP exists
        $storedData = Cache::get($cacheKey);

        if (!$storedData) {
            return [
                'success' => false,
                'message' => 'رمز التحقق منتهي الصلاحية أو غير موجود',
                'expired' => true,
            ];
        }

        // Check attempts
        $attempts = Cache::get($attemptsKey, 0);
        if ($attempts >= self::MAX_ATTEMPTS) {
            // Clear OTP after max attempts
            Cache::forget($cacheKey);
            Cache::forget($attemptsKey);

            return [
                'success' => false,
                'message' => 'تم تجاوز عدد المحاولات المسموح. يرجى طلب رمز جديد.',
                'max_attempts' => true,
            ];
        }

        // Verify code
        if ($storedData['code'] !== $code) {
            // Increment attempts
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(self::OTP_EXPIRY_MINUTES));

            $remainingAttempts = self::MAX_ATTEMPTS - $attempts - 1;

            return [
                'success' => false,
                'message' => "رمز التحقق غير صحيح. لديك {$remainingAttempts} محاولات متبقية.",
                'remaining_attempts' => $remainingAttempts,
            ];
        }

        // Success - Clear OTP and attempts
        Cache::forget($cacheKey);
        Cache::forget($attemptsKey);

        // Create or update participant
        $participant = $this->createOrUpdateParticipant($phone);

        return [
            'success' => true,
            'message' => 'تم التحقق بنجاح',
            'participant' => $participant,
            'is_new' => $participant->wasRecentlyCreated,
            'needs_registration' => empty($participant->name),
        ];
    }

    /**
     * Resend OTP
     */
    public function resendOtp(string $phone): array
    {
        // Clear existing OTP
        Cache::forget($this->getOtpCacheKey($phone));
        Cache::forget($this->getAttemptsCacheKey($phone));

        return $this->sendOtp($phone);
    }

    /**
     * Generate a random OTP code
     */
    protected function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Store OTP in cache
     */
    protected function storeOtp(string $phone, string $otp): void
    {
        $cacheKey = $this->getOtpCacheKey($phone);

        Cache::put($cacheKey, [
            'code' => $otp,
            'phone' => $phone,
            'created_at' => now()->toDateTimeString(),
        ], now()->addMinutes(self::OTP_EXPIRY_MINUTES));
    }

    /**
     * Send OTP via WhatsApp using Infobip
     */
    protected function sendWhatsAppOtp(string $phone, string $otp): bool
    {
        try {
            // Use existing Infobip service
            $result = $this->infobipService->sendWhatsAppOtp(
                phone: $phone,
                otpCode: $otp
            );

            if (!$result) {
                Log::error('Competition OTP WhatsApp failed', [
                    'phone' => $this->maskPhone($phone),
                ]);

                return false;
            }

            Log::info('Competition OTP sent', [
                'phone' => $this->maskPhone($phone),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Competition OTP exception', [
                'phone' => $this->maskPhone($phone),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create or update participant record
     */
    protected function createOrUpdateParticipant(string $phone): CompetitionParticipant
    {
        $participant = CompetitionParticipant::firstOrCreate(
            ['phone' => $phone],
            [
                'phone_verified_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'device_type' => $this->detectDeviceType(),
                'source' => request()->input('source', 'direct'),
            ]
        );

        // Update verification time if existing
        if (!$participant->wasRecentlyCreated) {
            $participant->update([
                'phone_verified_at' => now(),
                'verification_attempts' => 0,
                'verification_code' => null,
            ]);
        }

        // Generate referral code if not exists
        if (!$participant->referral_code) {
            $participant->generateReferralCode();
        }

        return $participant;
    }

    /**
     * Detect device type from user agent
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

    /**
     * Check if phone is in cooldown period
     */
    protected function isInCooldown(string $phone): bool
    {
        return Cache::has($this->getCooldownCacheKey($phone));
    }

    /**
     * Get remaining cooldown seconds
     */
    protected function getCooldownRemaining(string $phone): int
    {
        $cooldownKey = $this->getCooldownCacheKey($phone);
        $expiresAt = Cache::get($cooldownKey);

        if (!$expiresAt) {
            return 0;
        }

        return max(0, now()->diffInSeconds($expiresAt, false));
    }

    /**
     * Set cooldown for phone
     */
    protected function setCooldown(string $phone): void
    {
        $cooldownKey = $this->getCooldownCacheKey($phone);
        Cache::put($cooldownKey, now()->addMinutes(self::COOLDOWN_MINUTES), now()->addMinutes(self::COOLDOWN_MINUTES));
    }

    /**
     * Check if daily limit exceeded
     */
    protected function hasExceededDailyLimit(string $phone): bool
    {
        $dailyKey = $this->getDailyCounterCacheKey($phone);
        $count = Cache::get($dailyKey, 0);

        return $count >= self::MAX_DAILY_SENDS;
    }

    /**
     * Increment daily counter
     */
    protected function incrementDailyCounter(string $phone): void
    {
        $dailyKey = $this->getDailyCounterCacheKey($phone);
        $count = Cache::get($dailyKey, 0);

        Cache::put($dailyKey, $count + 1, now()->endOfDay());
    }

    /**
     * Mask phone number for display
     */
    protected function maskPhone(string $phone): string
    {
        // 966512345678 -> 9665****5678
        if (strlen($phone) > 8) {
            return substr($phone, 0, 4) . '****' . substr($phone, -4);
        }

        return $phone;
    }

    // Cache key helpers
    protected function getOtpCacheKey(string $phone): string
    {
        return "competition_otp:{$phone}";
    }

    protected function getAttemptsCacheKey(string $phone): string
    {
        return "competition_otp_attempts:{$phone}";
    }

    protected function getCooldownCacheKey(string $phone): string
    {
        return "competition_otp_cooldown:{$phone}";
    }

    protected function getDailyCounterCacheKey(string $phone): string
    {
        return "competition_otp_daily:{$phone}:" . now()->format('Y-m-d');
    }
}
