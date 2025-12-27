<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function show(Invoice $invoice)
    {
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($invoice->isPaid()) {
            return redirect()->route('payment.success', $invoice);
        }

        return view('checkout.show', [
            'invoice' => $invoice,
            'gateways' => $this->paymentService->getAvailableGateways($invoice->currency),
        ]);
    }

    public function process(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'gateway' => 'required|string|in:stripe,moyasar,manual',
        ]);

        $result = $this->paymentService->createCheckout($invoice, $validated['gateway']);

        if (! $result['success']) {
            return back()->with('error', $result['error'] ?? 'Payment failed');
        }

        if (! empty($result['checkout_url'])) {
            return redirect($result['checkout_url']);
        }

        if (! empty($result['requires_manual_confirmation'])) {
            return view('checkout.manual', [
                'invoice' => $invoice,
                'bank_details' => $result['bank_details'],
                'instructions' => $result['instructions'],
            ]);
        }

        if (! empty($result['requires_form'])) {
            return view('checkout.payment-form', [
                'invoice' => $invoice,
                'form_config' => $result['form_config'],
            ]);
        }

        return redirect()->route('payment.success', $invoice);
    }

    public function success(Request $request, Invoice $invoice)
    {
        if ($request->has('session_id')) {
            $this->paymentService->gateway('stripe')->verifyPayment($request->session_id);
        }

        $invoice->refresh();

        return view('checkout.success', [
            'invoice' => $invoice,
            'subscription' => $invoice->subscription,
        ]);
    }

    public function cancel(Invoice $invoice)
    {
        return view('checkout.cancel', ['invoice' => $invoice]);
    }
}
