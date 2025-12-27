<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Gateways\ManualGateway;
use App\Gateways\MoyasarGateway;
use App\Gateways\StripeGateway;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected array $gateways = [
        'stripe' => StripeGateway::class,
        'moyasar' => MoyasarGateway::class,
        'manual' => ManualGateway::class,
    ];

    public function gateway(string $identifier): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$identifier])) {
            throw new \InvalidArgumentException("Payment gateway '{$identifier}' not found");
        }

        return app($this->gateways[$identifier]);
    }

    public function getAvailableGateways(?string $currency = null): array
    {
        $available = [];

        foreach ($this->gateways as $identifier => $class) {
            $gateway = app($class);

            if (! $gateway->validateConfiguration()) {
                continue;
            }
            if ($currency && ! $gateway->supportsCurrency($currency)) {
                continue;
            }

            $available[] = [
                'identifier' => $identifier,
                'name' => $gateway->getName(),
                'name_ar' => $gateway->getNameAr(),
                'requires_redirect' => $gateway->requiresRedirect(),
            ];
        }

        return $available;
    }

    public function getDefaultGateway(string $currency): PaymentGatewayInterface
    {
        if ($currency === 'SAR' && $this->gateway('moyasar')->validateConfiguration()) {
            return $this->gateway('moyasar');
        }

        if ($this->gateway('stripe')->validateConfiguration()) {
            return $this->gateway('stripe');
        }

        return $this->gateway('manual');
    }

    public function createCheckout(Invoice $invoice, ?string $gatewayId = null, array $options = []): array
    {
        $gateway = $gatewayId
            ? $this->gateway($gatewayId)
            : $this->getDefaultGateway($invoice->currency);

        Log::info('Creating checkout', [
            'invoice_id' => $invoice->id,
            'gateway' => $gateway->getIdentifier(),
        ]);

        return $gateway->createCheckout($invoice, $options);
    }

    public function handleWebhook(string $gatewayId, array $payload, array $headers = []): array
    {
        return $this->gateway($gatewayId)->handleWebhook($payload, $headers);
    }

    public function refund(Payment $payment, ?float $amount = null, ?string $reason = null): array
    {
        return $this->gateway($payment->payment_gateway)
            ->refund($payment, $amount ?? $payment->amount, $reason);
    }

    public function confirmManualPayment(Payment $payment, array $data = []): Payment
    {
        if ($payment->payment_gateway !== 'manual') {
            throw new \InvalidArgumentException('Not a manual payment');
        }

        return $this->gateway('manual')->confirmPayment($payment, $data);
    }

    public function syncPaymentStatus(Payment $payment): Payment
    {
        $status = $this->gateway($payment->payment_gateway)
            ->getPaymentStatus($payment->gateway_payment_id);

        $newStatus = match ($status) {
            'completed', 'paid' => PaymentStatus::COMPLETED,
            'failed' => PaymentStatus::FAILED,
            'refunded' => PaymentStatus::REFUNDED,
            default => $payment->status,
        };

        if ($newStatus !== $payment->status) {
            $payment->update([
                'status' => $newStatus,
                'paid_at' => $newStatus === PaymentStatus::COMPLETED ? now() : $payment->paid_at,
            ]);
        }

        return $payment->fresh();
    }
}
