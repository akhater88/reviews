<?php

namespace App\Http\Controllers;

use App\Models\FreeReport;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FreeReportPageController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsappService
    ) {}

    /**
     * Display the free report page.
     */
    public function show(string $token, Request $request): View|RedirectResponse
    {
        $report = FreeReport::where('magic_link_token', $token)
            ->with(['result', 'reviews'])
            ->first();

        // Report not found or expired
        if (!$report || !$report->isMagicLinkValid()) {
            return view('free-report.not-found');
        }

        // Report still processing
        if ($report->isProcessing()) {
            return view('free-report.processing', [
                'report' => $report,
            ]);
        }

        // Report failed
        if ($report->hasFailed()) {
            return view('free-report.failed', [
                'report' => $report,
            ]);
        }

        // Get active tab
        $activeTab = $request->get('tab', 'overview');
        $validTabs = ['overview', 'keywords', 'recommendations', 'sentiment', 'operational', 'categories'];
        if (!in_array($activeTab, $validTabs)) {
            $activeTab = 'overview';
        }

        return view('free-report.show', [
            'report' => $report,
            'result' => $report->result,
            'reviews' => $report->reviews,
            'activeTab' => $activeTab,
            'token' => $token,
        ]);
    }

    /**
     * Show the access request form.
     */
    public function accessForm(): View
    {
        return view('free-report.access');
    }

    /**
     * Request magic link for existing report.
     */
    public function requestAccess(Request $request): RedirectResponse
    {
        $request->validate([
            'phone_country_code' => 'required|string|max:10',
            'phone_number' => 'required|string|max:20',
        ]);

        $phone = $request->phone_country_code . $request->phone_number;
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        $report = FreeReport::where('phone', $phone)
            ->where('status', FreeReport::STATUS_COMPLETED)
            ->latest()
            ->first();

        if (!$report) {
            return back()->with('error', 'لم يتم العثور على تقرير لهذا الرقم');
        }

        // Generate new magic token
        $report->generateMagicLinkToken();

        // Send via WhatsApp
        $this->whatsappService->sendMagicLink($report);

        return back()->with('success', 'تم إرسال رابط التقرير إلى واتساب');
    }
}
