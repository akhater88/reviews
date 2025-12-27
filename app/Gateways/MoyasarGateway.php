<?php

namespace App\Gateways;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class MoyasarGateway extends AbstractPaymentGateway
{
    protected string $baseUrl = 'https://api.moyasar.com/v1';

    public function getIdentifier(): string
    {
        return 'moyasar';
    }

    public function getName(): string
    {
        return 'Moyasar';
    }

    public function getNameAr(): string
    {
        return 'ميسر';
    }

    public function supportsCurrency(string $currency): bool
    {
        return in_array(strtoupper($currency), $this->getSupportedCurrencies());
    }

    public function getSupportedCurrencies(): array
    {
        return ['SAR', 'USD', 'AED', 'KWD', 'BHD', 'OMR', 'QAR'];
    }

    public function requiresRedirect(): bool
    {
        return true;
    }

    public function validateConfiguration(): bool
    {
        return ! empty($this->config['secret_key']) && ! empty($this->config['publishable_key']);
    }

    public function createCheckout(Invoice $invoice, array $options = []): array
    {
        // Return form config for Moyasar embedded form
        return [
            'success' => true,
            'requires_form' => true,
            'form_config' => [
                'element' => '.moyasar-form',
                'amount' => $this->formatAmount($invoice->total_amount, $invoice->currency),
                'currency' => strtoupper($invoice->currency),
                'description' => "Invoice #{$invoice->invoice_number}",
                'publishable_api_key' => $this->config['publishable_key'],
                'callback_url' => $this->getSuccessUrl($invoice),
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $invoice->tenant_id,
                ],
                'methods' => ['creditcard', 'applepay', 'stcpay'],
            ],
        ];
    }

    public function processPayment(Invoice $invoice, array $paymentData): Payment
    {
        $result = $this->createCheckout($invoice, $paymentData);

        return Payment::where('invoice_id', $invoice->id)->latest()->firstOrFail();
    }

    public function verifyPayment(string $paymentReference): array
    {
        try {
            $response = Http::withBasicAuth($this->config['secret_key'], '')
                ->get("{$this->baseUrl}/payments/{$paymentReference}");

            if (! $response->successful()) {
                throw new \Exception('Payment verification failed');
            }

            $payment = $response->json();

            return [
                'success' => true,
                'status' => $this->mapStatus($payment['status'])->value,
                'amount' => $payment['amount'],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function handleWebhook(array $payload, array $headers = []): array
    {
        try {
            $paymentData = $payload['data'] ?? $payload;
            $paymentId = $paymentData['id'] ?? null;

            if (! $paymentId) {
                return ['success' => false, 'error' => 'No payment ID'];
            }

            $invoiceId = $paymentData['metadata']['invoice_id'] ?? null;
            $invoice = Invoice::find($invoiceId);

            if (! $invoice) {
                return ['success' => false, 'error' => 'Invoice not found'];
            }

            $payment = Payment::where('gateway_payment_id', $paymentId)->first();

            if (! $payment) {
                $payment = $this->createPaymentRecord(
                    $invoice,
                    $paymentId,
                    $this->mapStatus($paymentData['status']),
                    $paymentData
                );
            }

            $newStatus = $this->mapStatus($paymentData['status']);

            $payment->update([
                'status' => $newStatus,
                'paid_at' => $newStatus === PaymentStatus::COMPLETED ? now() : null,
            ]);

            if ($newStatus === PaymentStatus::COMPLETED) {
                $invoice->update([
                    'status' => \App\Enums\InvoiceStatus::PAID,
                    'paid_at' => now(),
                ]);
                $this->activateSubscription($payment);
            }

            return ['success' => true, 'payment_id' => $payment->id];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refund(Payment $payment, float $amount, ?string $reason = null): array
    {
        try {
            $response = Http::withBasicAuth($this->config['secret_key'], '')
                ->post("{$this->baseUrl}/payments/{$payment->gateway_payment_id}/refund", [
                    'amount' => $this->formatAmount($amount, $payment->currency),
                ]);

            if (! $response->successful()) {
                throw new \Exception($response->json('message') ?? 'Refund failed');
            }

            $refundedAmount = ($payment->metadata['refunded_amount'] ?? 0) + $amount;

            $payment->update([
                'status' => $amount >= $payment->amount ? PaymentStatus::REFUNDED : PaymentStatus::COMPLETED,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refunded_amount' => $refundedAmount,
                    'refunded_at' => now()->toDateTimeString(),
                ]),
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPaymentStatus(string $paymentReference): string
    {
        $result = $this->verifyPayment($paymentReference);

        return $result['status'] ?? 'unknown';
    }

    protected function mapStatus(string $status): PaymentStatus
    {
        return match (strtolower($status)) {
            'paid' => PaymentStatus::COMPLETED,
            'failed' => PaymentStatus::FAILED,
            'refunded' => PaymentStatus::REFUNDED,
            default => PaymentStatus::PENDING,
        };
    }
}
