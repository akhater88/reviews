<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FreeReport;
use App\Services\FreeReportService;
use App\Services\PhoneOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FreeReportController extends Controller
{
    protected FreeReportService $freeReportService;
    protected PhoneOtpService $phoneOtpService;

    public function __construct(
        FreeReportService $freeReportService,
        PhoneOtpService $phoneOtpService
    ) {
        $this->freeReportService = $freeReportService;
        $this->phoneOtpService = $phoneOtpService;
    }

    /**
     * Request OTP for phone verification.
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:9|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الهاتف غير صالح',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->phoneOtpService->generateOtp($request->phone);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 429);
    }

    /**
     * Verify OTP code.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:9|max:15',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات غير صالحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->phoneOtpService->verifyOtp($request->phone, $request->otp);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Create a new free report request.
     */
    public function createReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:9|max:15',
            'place_id' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'business_address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات غير صالحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $report = $this->freeReportService->createReport(
                phone: $request->phone,
                placeId: $request->place_id,
                businessName: $request->business_name,
                businessAddress: $request->business_address
            );

            Log::info('FreeReportController: Report created', [
                'report_id' => $report->id,
                'phone' => $request->phone,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء طلب التقرير بنجاح. سيتم إرسال الرابط عبر واتساب.',
                'data' => [
                    'report_id' => $report->id,
                    'status' => $report->status,
                    'business_name' => $report->business_name,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('FreeReportController: Failed to create report', [
                'error' => $e->getMessage(),
                'phone' => $request->phone,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء التقرير. حاول مرة أخرى.',
            ], 500);
        }
    }

    /**
     * Get report status.
     */
    public function getStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:9|max:15',
            'place_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات غير صالحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = preg_replace('/[\s\-\(\)]/', '', $request->phone);
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        $report = FreeReport::where('phone', $phone)
            ->where('place_id', $request->place_id)
            ->latest()
            ->first();

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على تقرير',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'report_id' => $report->id,
                'status' => $report->status,
                'is_completed' => $report->isCompleted(),
                'is_processing' => $report->isProcessing(),
                'has_failed' => $report->hasFailed(),
                'error_message' => $report->error_message,
                'created_at' => $report->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get report by magic link token.
     */
    public function getReportByToken(string $token): JsonResponse
    {
        $report = $this->freeReportService->getReportByToken($token);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'الرابط غير صالح أو منتهي الصلاحية',
            ], 404);
        }

        if (!$report->isCompleted()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'report_id' => $report->id,
                    'status' => $report->status,
                    'is_completed' => false,
                    'is_processing' => $report->isProcessing(),
                    'has_failed' => $report->hasFailed(),
                    'business_name' => $report->business_name,
                    'message' => $report->hasFailed()
                        ? 'فشل في إنشاء التقرير: ' . $report->error_message
                        : 'جاري إعداد التقرير...',
                ],
            ]);
        }

        $reportData = $this->freeReportService->getReportWithResults($report);

        return response()->json([
            'success' => true,
            'data' => $reportData,
        ]);
    }

    /**
     * Resend magic link.
     */
    public function resendMagicLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:9|max:15',
            'place_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات غير صالحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = preg_replace('/[\s\-\(\)]/', '', $request->phone);
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        $report = FreeReport::where('phone', $phone)
            ->where('place_id', $request->place_id)
            ->where('status', FreeReport::STATUS_COMPLETED)
            ->latest()
            ->first();

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على تقرير مكتمل',
            ], 404);
        }

        // Check rate limit for resending
        if ($report->magic_link_sent_at && $report->magic_link_sent_at->diffInMinutes(now()) < 5) {
            $remaining = 5 - $report->magic_link_sent_at->diffInMinutes(now());
            return response()->json([
                'success' => false,
                'message' => "انتظر {$remaining} دقائق قبل إعادة الإرسال",
            ], 429);
        }

        // Regenerate token and send
        $report->generateMagicLinkToken();
        $sent = $this->freeReportService->sendMagicLink($report);

        if ($sent) {
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الرابط عبر واتساب',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'فشل في إرسال الرابط. حاول مرة أخرى.',
        ], 500);
    }
}
