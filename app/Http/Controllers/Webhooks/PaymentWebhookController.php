<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function handle(Request $request, string $gateway)
    {
        Log::info('Payment webhook received', ['gateway' => $gateway]);

        try {
            $result = $this->paymentService->handleWebhook(
                $gateway,
                $request->all(),
                $request->headers->all()
            );

            return response()->json(
                ['status' => $result['success'] ? 'ok' : 'error'],
                $result['success'] ? 200 : 400
            );

        } catch (\Exception $e) {
            Log::error('Webhook error', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Internal error'], 500);
        }
    }
}
