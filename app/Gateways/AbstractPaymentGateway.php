<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected array $config;

    public function __construct()
    {
        $this->config = config("payment.gateways.{$this->getIdentifier()}", []);
    }

    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        $context['gateway'] = $this->getIdentifier();
        Log::{$level}("[Payment Gateway] {$message}", $context);
    }

    protected function createPaymentRecord(
        Invoice $invoice,
        string $gatewayReference,
        PaymentStatus $status,
        array $metadata = []
    ): Payment {
        return Payment::create([
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'payment_gateway' => $this->getIdentifier(),
            'gateway_payment_id' => $gatewayReference,
            'amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
            'status' => $status,
            'paid_at' => $status === PaymentStatus::COMPLETED ? now() : null,
            'metadata' => $metadata,
        ]);
    }

    protected function formatAmount(float $amount, string $currency): int
    {
        $multiplier = match (strtoupper($currency)) {
            'KWD', 'BHD', 'OMR' => 1000,
            'JPY' => 1,
            default => 100,
        };

        return (int) round($amount * $multiplier);
    }

    protected function getSuccessUrl(Invoice $invoice): string
    {
        return route('payment.success', [
            'invoice' => $invoice->id,
            'gateway' => $this->getIdentifier(),
        ]);
    }

    protected function getCancelUrl(Invoice $invoice): string
    {
        return route('payment.cancel', [
            'invoice' => $invoice->id,
        ]);
    }

    protected function activateSubscription(Payment $payment): void
    {
        $subscription = $payment->invoice?->subscription;
        if (! $subscription) {
            return;
        }

        $service = app(\App\Services\SubscriptionService::class);

        if ($subscription->status === \App\Enums\SubscriptionStatus::TRIAL) {
            $service->convertTrial($subscription, 'payment', $payment->id);
        } elseif (in_array($subscription->status, [
            \App\Enums\SubscriptionStatus::EXPIRED,
            \App\Enums\SubscriptionStatus::GRACE_PERIOD,
        ])) {
            $service->renew($subscription, 'payment', $payment->id);
        }
    }
}
