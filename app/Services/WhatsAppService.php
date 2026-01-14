<?php

namespace App\Services;

use App\Models\FreeReport;
use App\Services\Infobip\InfobipService;
use App\Services\Infobip\InfobipConnector;
use App\Services\Infobip\Requests\InfobipMagicLinkRequest;
use Exception;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected InfobipService $infobipService;
    protected InfobipConnector $connector;

    public function __construct(InfobipService $infobipService)
    {
        $this->infobipService = $infobipService;
        $this->connector = new InfobipConnector();
    }

    /**
     * Send magic link via WhatsApp for free report access.
     */
    public function sendMagicLink(FreeReport $report): bool
    {
        $phone = $this->formatPhoneNumber($report->phone);
        $magicLinkUrl = $report->getMagicLinkUrl();

        if (!$magicLinkUrl) {
            Log::error('WhatsApp magic link: No magic link URL available', [
                'report_id' => $report->id,
            ]);
            return false;
        }

        // In development, just log the magic link
        if (!app()->isProduction()) {
            Log::info('WhatsApp Magic Link (Dev Mode)', [
                'phone' => $phone,
                'business_name' => $report->business_name,
                'magic_link' => $magicLinkUrl,
            ]);

            $report->update(['magic_link_sent_at' => now()]);
            return true;
        }

        try {
            $request = new InfobipMagicLinkRequest(
                phone: $phone,
                businessName: $report->business_name,
                magicLinkUrl: $magicLinkUrl
            );

            $response = $this->connector->send($request);

            if ($response->successful()) {
                Log::info('WhatsApp magic link sent successfully', [
                    'report_id' => $report->id,
                    'phone' => $phone,
                ]);

                $report->update(['magic_link_sent_at' => now()]);
                return true;
            }

            Log::error('WhatsApp magic link failed', [
                'report_id' => $report->id,
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('WhatsApp magic link exception', [
                'report_id' => $report->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send report ready notification via WhatsApp.
     */
    public function sendReportReadyNotification(FreeReport $report): bool
    {
        $message = "تقرير التحليل جاهز لـ {$report->business_name}! 🎉\n\n";
        $message .= "اضغط على الرابط التالي لمشاهدة التقرير:\n";
        $message .= $report->getMagicLinkUrl();

        return $this->infobipService->sendWhatsAppMessage(
            $report->phone,
            $message
        );
    }

    /**
     * Format phone number for Infobip.
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
