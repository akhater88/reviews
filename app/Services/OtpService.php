<?php

namespace App\Services;

use App\Services\Infobip\InfobipService;
use Illuminate\Support\Facades\Log;

class OtpService
{
    protected InfobipService $infobipService;

    public function __construct(InfobipService $infobipService)
    {
        $this->infobipService = $infobipService;
    }

    /**
     * Send OTP via WhatsApp using Infobip.
     * Falls back to SMS if WhatsApp fails.
     *
     * @param string $phone Phone number with country code (e.g., +966512345678)
     * @param string $otp The OTP code to send
     * @return bool True if sent successfully
     */
    public function sendWhatsAppOtp(string $phone, string $otp): bool
    {
        // Try WhatsApp first
        $sent = $this->infobipService->sendWhatsAppOtp($phone, $otp);

        if ($sent) {
            return true;
        }

        // Fallback to SMS if WhatsApp fails
        Log::info("WhatsApp OTP failed for {$phone}, trying SMS fallback");
        return $this->infobipService->sendSmsOtp($phone, $otp);
    }

    /**
     * Send OTP via SMS only.
     *
     * @param string $phone Phone number with country code
     * @param string $otp The OTP code to send
     * @return bool True if sent successfully
     */
    public function sendSmsOtp(string $phone, string $otp): bool
    {
        return $this->infobipService->sendSmsOtp($phone, $otp);
    }
}
