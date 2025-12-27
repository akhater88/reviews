<?php

namespace App\Gateways;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;

class ManualGateway extends AbstractPaymentGateway
{
    public function getIdentifier(): string
    {
        return 'manual';
    }

    public function getName(): string
    {
        return 'Bank Transfer';
    }

    public function getNameAr(): string
    {
        return 'تحويل بنكي';
    }

    public function supportsCurrency(string $currency): bool
    {
        return true;
    }

    public function getSupportedCurrencies(): array
    {
        return ['SAR', 'USD', 'AED', 'EUR'];
    }

    public function requiresRedirect(): bool
    {
        return false;
    }

    public function validateConfiguration(): bool
    {
        return ! empty($this->config['bank_name']) && ! empty($this->config['account_number']);
    }

    public function createCheckout(Invoice $invoice, array $options = []): array
    {
        $payment = $this->createPaymentRecord(
            $invoice,
            'MANUAL-'.strtoupper(uniqid()),
            PaymentStatus::PENDING,
            ['awaiting_confirmation' => true]
        );

        return [
            'success' => true,
            'requires_manual_confirmation' => true,
            'payment_id' => $payment->id,
            'bank_details' => $this->getBankDetails($invoice),
            'instructions' => $this->getInstructions($invoice),
        ];
    }

    public function getBankDetails(Invoice $invoice): array
    {
        return [
            'bank_name' => $this->config['bank_name'] ?? '',
            'account_name' => $this->config['account_name'] ?? '',
            'account_number' => $this->config['account_number'] ?? '',
            'iban' => $this->config['iban'] ?? '',
            'swift_code' => $this->config['swift_code'] ?? '',
            'amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
            'reference' => $invoice->invoice_number,
        ];
    }

    public function getInstructions(Invoice $invoice): string
    {
        return $this->config['instructions_ar'] ?? "يرجى تحويل المبلغ إلى الحساب البنكي أدناه مع ذكر رقم الفاتورة ({$invoice->invoice_number}) في خانة الملاحظات.";
    }

    public function processPayment(Invoice $invoice, array $paymentData): Payment
    {
        $result = $this->createCheckout($invoice, $paymentData);

        return Payment::find($result['payment_id']);
    }

    public function confirmPayment(Payment $payment, array $data = []): Payment
    {
        $payment->update([
            'status' => PaymentStatus::COMPLETED,
            'paid_at' => $data['paid_at'] ?? now(),
            'metadata' => array_merge($payment->metadata ?? [], [
                'confirmed_by' => auth()->guard('super_admin')->id() ?? auth()->id(),
                'confirmed_at' => now()->toDateTimeString(),
                'bank_reference' => $data['bank_reference'] ?? null,
            ]),
        ]);

        $payment->invoice->update([
            'status' => \App\Enums\InvoiceStatus::PAID,
            'paid_at' => $data['paid_at'] ?? now(),
        ]);

        $this->activateSubscription($payment);

        return $payment->fresh();
    }

    public function verifyPayment(string $paymentReference): array
    {
        $payment = Payment::where('gateway_payment_id', $paymentReference)->first();

        if (! $payment) {
            return ['success' => false, 'error' => 'Payment not found'];
        }

        return [
            'success' => true,
            'status' => $payment->status->value,
            'confirmed' => $payment->status === PaymentStatus::COMPLETED,
        ];
    }

    public function handleWebhook(array $payload, array $headers = []): array
    {
        return ['success' => true, 'message' => 'No webhook for manual gateway'];
    }

    public function refund(Payment $payment, float $amount, ?string $reason = null): array
    {
        $refundedAmount = ($payment->metadata['refunded_amount'] ?? 0) + $amount;

        $payment->update([
            'status' => $amount >= $payment->amount ? PaymentStatus::REFUNDED : PaymentStatus::COMPLETED,
            'metadata' => array_merge($payment->metadata ?? [], [
                'refunded_amount' => $refundedAmount,
                'refunded_at' => now()->toDateTimeString(),
                'refund_reason' => $reason,
            ]),
        ]);

        return ['success' => true, 'message' => 'Refund recorded. Process manually.'];
    }

    public function getPaymentStatus(string $paymentReference): string
    {
        $result = $this->verifyPayment($paymentReference);

        return $result['status'] ?? 'unknown';
    }
}
