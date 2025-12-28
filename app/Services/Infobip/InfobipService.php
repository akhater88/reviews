<?php

namespace App\Services\Infobip;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Services\Infobip\InfobipConnector;
use App\Services\Infobip\Requests\InfobipSmsRequest;
use App\Services\Infobip\Requests\InfobipWhatsAppRequest;

class InfobipService
{
    protected InfobipConnector $connector;

    public function __construct()
    {
        $this->connector = new InfobipConnector();
    }

    /**
     * Send OTP via WhatsApp using Infobip Template
     */
    public function sendWhatsAppOtp(string $phone, string $otpCode): bool
    {
        // In development, just log the OTP
        if (!app()->isProduction()) {
            Log::info('WhatsApp OTP (Dev Mode)', [
                'phone' => $phone,
                'otp' => $otpCode,
            ]);
            return true;
        }

        try {
            $request = new InfobipWhatsAppRequest(
                phone: $this->formatPhoneNumber($phone),
                otpCode: $otpCode
            );

            $response = $this->connector->send($request);

            if ($response->successful()) {
                Log::info('WhatsApp OTP sent successfully', [
                    'phone' => $phone,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('WhatsApp OTP failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('WhatsApp OTP exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send OTP via SMS (fallback)
     */
    public function sendSmsOtp(string $phone, string $otpCode): bool
    {
        // In development, just log the OTP
        if (!app()->isProduction()) {
            Log::info('SMS OTP (Dev Mode)', [
                'phone' => $phone,
                'otp' => $otpCode,
            ]);
            return true;
        }

        try {
            $message = "رمز التحقق الخاص بك في TABsense هو: {$otpCode}";

            $request = new InfobipSmsRequest(
                phone: $this->formatPhoneNumber($phone),
                message: $message
            );

            $response = $this->connector->send($request);

            if ($response->successful()) {
                Log::info('SMS OTP sent successfully', ['phone' => $phone]);
                return true;
            }

            Log::error('SMS OTP failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('SMS OTP exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send generic WhatsApp message (for notifications, reminders, etc.)
     */
    public function sendWhatsAppMessage(string $phone, string $message): bool
    {
        // In development, just log the message
        if (!app()->isProduction()) {
            Log::info('WhatsApp Message (Dev Mode)', [
                'phone' => $phone,
                'message' => $message,
            ]);

            return true;
        }

        try {
            $request = new InfobipWhatsAppRequest(
                phone: $this->formatPhoneNumber($phone),
                message: $message
            );

            $response = $this->connector->send($request);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'phone' => $phone,
                ]);

                return true;
            }

            Log::error('WhatsApp message failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('WhatsApp message exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send generic SMS message
     */
    public function sendSmsMessage(string $phone, string $message): bool
    {
        // In development, just log the message
        if (!app()->isProduction()) {
            Log::info('SMS Message (Dev Mode)', [
                'phone' => $phone,
                'message' => $message,
            ]);

            return true;
        }

        try {
            $request = new InfobipSmsRequest(
                phone: $this->formatPhoneNumber($phone),
                message: $message
            );

            $response = $this->connector->send($request);

            if ($response->successful()) {
                Log::info('SMS message sent successfully', ['phone' => $phone]);

                return true;
            }

            Log::error('SMS message failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('SMS message exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Format phone number for Infobip (must include country code, no + sign)
     * Example: +966512345678 -> 966512345678
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove any non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // If starts with 0, assume Saudi Arabia and replace with 966
        if (str_starts_with($phone, '0')) {
            $phone = '966' . substr($phone, 1);
        }

        // If doesn't start with country code, add 966 (Saudi Arabia)
        if (strlen($phone) === 9) {
            $phone = '966' . $phone;
        }

        return $phone;
    }
}
