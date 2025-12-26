<?php

namespace App\Services;

use App\Models\OtpVerification;
use Carbon\Carbon;

class PhoneOtpService
{
    public const MAX_ATTEMPTS = 3;
    public const OTP_EXPIRY_MINUTES = 5;
    public const COOLDOWN_SECONDS = 30;
    public const MAX_SENDS_PER_HOUR = 5;

    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Generate and send a new OTP.
     *
     * @param string $phone Phone number with country code
     * @return array ['success' => bool, 'message' => string, 'otp_code' => string|null]
     */
    public function generateOtp(string $phone): array
    {
        $phone = $this->normalizePhone($phone);

        // Check rate limiting
        $rateLimit = $this->getRateLimitInfo($phone);
        if ($rateLimit['is_rate_limited']) {
            return [
                'success' => false,
                'message' => "انتظر {$rateLimit['remaining_time']} ثانية قبل إعادة الإرسال",
                'otp_code' => null,
            ];
        }

        // Check hourly limit
        $hourlyCount = $this->getHourlySendCount($phone);
        if ($hourlyCount >= self::MAX_SENDS_PER_HOUR) {
            return [
                'success' => false,
                'message' => 'تم تجاوز الحد الأقصى لإرسال رمز التحقق. حاول مرة أخرى بعد ساعة.',
                'otp_code' => null,
            ];
        }

        // Generate 6-digit OTP
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Get or create OTP verification record
        $verification = OtpVerification::where('phone', $phone)
            ->where('is_verified', false)
            ->first();

        if ($verification) {
            // Update existing record
            $verification->update([
                'otp_code' => $otpCode,
                'attempts' => 0,
                'send_count' => $verification->send_count + 1,
                'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
                'last_sent_at' => now(),
            ]);
        } else {
            // Create new record
            $verification = OtpVerification::create([
                'phone' => $phone,
                'otp_code' => $otpCode,
                'attempts' => 0,
                'send_count' => 1,
                'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
                'last_sent_at' => now(),
                'is_verified' => false,
            ]);
        }

        // Send OTP via WhatsApp
        $sent = $this->otpService->sendWhatsAppOtp($phone, $otpCode);

        if (!$sent) {
            return [
                'success' => false,
                'message' => 'فشل في إرسال رمز التحقق. حاول مرة أخرى.',
                'otp_code' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'تم إرسال رمز التحقق عبر واتساب',
            'otp_code' => $otpCode, // Only for development/logging
        ];
    }

    /**
     * Verify an OTP.
     *
     * @param string $phone Phone number with country code
     * @param string $otp The OTP to verify
     * @return array ['success' => bool, 'message' => string, 'verification' => OtpVerification|null]
     */
    public function verifyOtp(string $phone, string $otp): array
    {
        $phone = $this->normalizePhone($phone);

        $verification = OtpVerification::where('phone', $phone)
            ->where('is_verified', false)
            ->first();

        if (!$verification) {
            return [
                'success' => false,
                'message' => 'لم يتم طلب رمز تحقق لهذا الرقم',
                'verification' => null,
            ];
        }

        // Check if expired
        if ($verification->isExpired()) {
            return [
                'success' => false,
                'message' => 'انتهت صلاحية رمز التحقق. اطلب رمزًا جديدًا.',
                'verification' => $verification,
            ];
        }

        // Check max attempts
        if ($verification->hasMaxAttempts(self::MAX_ATTEMPTS)) {
            return [
                'success' => false,
                'message' => 'تم تجاوز الحد الأقصى للمحاولات. اطلب رمزًا جديدًا.',
                'verification' => $verification,
            ];
        }

        // Verify OTP
        if ($verification->otp_code !== $otp) {
            $verification->increment('attempts');
            $remainingAttempts = self::MAX_ATTEMPTS - $verification->attempts;

            return [
                'success' => false,
                'message' => "رمز التحقق غير صحيح. المحاولات المتبقية: {$remainingAttempts}",
                'verification' => $verification,
            ];
        }

        // OTP is correct - mark as verified
        $verification->update(['is_verified' => true]);

        return [
            'success' => true,
            'message' => 'تم التحقق بنجاح',
            'verification' => $verification,
        ];
    }

    /**
     * Get rate limit information for a phone number.
     *
     * @param string $phone Phone number
     * @return array ['is_rate_limited' => bool, 'remaining_time' => int, 'attempts_count' => int, 'max_attempts' => int]
     */
    public function getRateLimitInfo(string $phone): array
    {
        $phone = $this->normalizePhone($phone);

        $verification = OtpVerification::where('phone', $phone)
            ->where('is_verified', false)
            ->first();

        if (!$verification) {
            return [
                'is_rate_limited' => false,
                'remaining_time' => 0,
                'attempts_count' => 0,
                'max_attempts' => self::MAX_ATTEMPTS,
            ];
        }

        $remainingCooldown = $verification->getRemainingCooldown(self::COOLDOWN_SECONDS);

        return [
            'is_rate_limited' => $remainingCooldown > 0,
            'remaining_time' => $remainingCooldown,
            'attempts_count' => $verification->attempts,
            'max_attempts' => self::MAX_ATTEMPTS,
        ];
    }

    /**
     * Handle incorrect OTP attempt.
     *
     * @param string $phone Phone number
     * @return OtpVerification|null
     */
    public function handleIncorrectOtp(string $phone): ?OtpVerification
    {
        $phone = $this->normalizePhone($phone);

        $verification = OtpVerification::where('phone', $phone)
            ->where('is_verified', false)
            ->first();

        if ($verification) {
            $verification->increment('attempts');
        }

        return $verification;
    }

    /**
     * Get count of OTPs sent in the last hour.
     */
    protected function getHourlySendCount(string $phone): int
    {
        return OtpVerification::where('phone', $phone)
            ->where('created_at', '>=', now()->subHour())
            ->sum('send_count');
    }

    /**
     * Normalize phone number format.
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove spaces, dashes, and parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Ensure it starts with +
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Clean up old unverified OTP records (can be called via scheduler).
     */
    public function cleanupExpiredOtps(): int
    {
        return OtpVerification::where('is_verified', false)
            ->where('expires_at', '<', now()->subHours(24))
            ->delete();
    }
}
