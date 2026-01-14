<?php

namespace App\Jobs;

use App\Models\FreeReport;
use App\Services\FreeReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateFreeReportResultsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120; // 2 minutes
    public int $backoff = 30;  // 30 seconds between retries

    public function __construct(
        public FreeReport $report,
        public array $analysisData
    ) {}

    public function handle(FreeReportService $freeReportService): void
    {
        Log::info('GenerateFreeReportResultsJob: Starting', [
            'report_id' => $this->report->id,
        ]);

        // Update status
        $this->report->updateStatus(FreeReport::STATUS_GENERATING_RESULTS);

        try {
            // Generate and store results
            $result = $freeReportService->generateResults($this->report, $this->analysisData);

            Log::info('GenerateFreeReportResultsJob: Results generated', [
                'report_id' => $this->report->id,
                'result_id' => $result->id,
                'overall_score' => $result->overall_score,
            ]);

            // Mark report as completed
            $this->report->updateStatus(FreeReport::STATUS_COMPLETED);

            // Dispatch job to send magic link
            SendFreeReportMagicLinkJob::dispatch($this->report);

        } catch (\Exception $e) {
            Log::error('GenerateFreeReportResultsJob: Failed', [
                'report_id' => $this->report->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateFreeReportResultsJob: Permanently failed', [
            'report_id' => $this->report->id,
            'error' => $exception->getMessage(),
        ]);

        $this->report->updateStatus(
            FreeReport::STATUS_FAILED,
            'فشل في إنشاء التقرير: ' . $exception->getMessage()
        );
    }
}
