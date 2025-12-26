<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Send OTP via WhatsApp.
     *
     * @param string $phone Phone number with country code (e.g., +966512345678)
     * @param string $otp The OTP code to send
     * @return bool True if sent successfully
     */
    public function sendWhatsAppOtp(string $phone, string $otp): bool
    {
        // In development mode, just log the OTP
        if (!app()->isProduction()) {
            Log::info("WhatsApp OTP for {$phone}: {$otp}");
            return true;
        }

        try {
            // WhatsApp Business API integration
            // You can replace this with your actual WhatsApp API provider
            // Common providers: Twilio, MessageBird, Vonage, etc.

            $apiUrl = config('services.whatsapp.api_url');
            $apiKey = config('services.whatsapp.api_key');
            $templateName = config('services.whatsapp.otp_template', 'otp_verification');

            if (!$apiUrl || !$apiKey) {
                Log::warning('WhatsApp API not configured, logging OTP instead');
                Log::info("WhatsApp OTP for {$phone}: {$otp}");
                return true;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatPhoneNumber($phone),
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => 'ar',
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $otp,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp OTP sent successfully to {$phone}");
                return true;
            }

            Log::error("Failed to send WhatsApp OTP to {$phone}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error("WhatsApp OTP exception for {$phone}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number for WhatsApp API.
     * Removes + and any spaces/dashes.
     */
    protected function formatPhoneNumber(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
