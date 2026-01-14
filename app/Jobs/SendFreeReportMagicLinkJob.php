<?php

namespace App\Jobs;

use App\Models\FreeReport;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFreeReportMagicLinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 60; // 1 minute
    public array $backoff = [30, 60, 120, 300, 600]; // Progressive backoff

    public function __construct(
        public FreeReport $report
    ) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        Log::info('SendFreeReportMagicLinkJob: Starting', [
            'report_id' => $this->report->id,
            'phone' => $this->report->phone,
        ]);

        // Ensure magic link token exists
        if (!$this->report->magic_link_token) {
            $this->report->generateMagicLinkToken();
        }

        try {
            $sent = $whatsAppService->sendMagicLink($this->report);

            if (!$sent) {
                throw new \Exception('Failed to send magic link via WhatsApp');
            }

            Log::info('SendFreeReportMagicLinkJob: Magic link sent', [
                'report_id' => $this->report->id,
                'magic_link_url' => $this->report->getMagicLinkUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('SendFreeReportMagicLinkJob: Failed', [
                'report_id' => $this->report->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendFreeReportMagicLinkJob: Permanently failed', [
            'report_id' => $this->report->id,
            'error' => $exception->getMessage(),
        ]);

        // Don't update report status - the report is still completed
        // Just log the failure to send the magic link
        // The user can still access their report if they have the link
    }
}
