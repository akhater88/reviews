<?php

namespace App\Gateways;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeGateway extends AbstractPaymentGateway
{
    public function __construct()
    {
        parent::__construct();
        Stripe::setApiKey($this->config['secret_key'] ?? '');
    }

    public function getIdentifier(): string
    {
        return 'stripe';
    }

    public function getName(): string
    {
        return 'Stripe';
    }

    public function getNameAr(): string
    {
        return 'سترايب';
    }

    public function supportsCurrency(string $currency): bool
    {
        return in_array(strtoupper($currency), $this->getSupportedCurrencies());
    }

    public function getSupportedCurrencies(): array
    {
        return ['USD', 'SAR', 'AED', 'EUR', 'GBP'];
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
        try {
            $tenant = $invoice->tenant;

            $lineItems = [];
            foreach ($invoice->items ?? [] as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => strtolower($invoice->currency),
                        'product_data' => [
                            'name' => $item['description_ar'] ?? $item['description'],
                        ],
                        'unit_amount' => $this->formatAmount(abs($item['unit_price']), $invoice->currency),
                    ],
                    'quantity' => $item['quantity'],
                ];
            }

            if ($invoice->tax_amount > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => strtolower($invoice->currency),
                        'product_data' => ['name' => 'VAT (15%)'],
                        'unit_amount' => $this->formatAmount($invoice->tax_amount, $invoice->currency),
                    ],
                    'quantity' => 1,
                ];
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->getSuccessUrl($invoice).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->getCancelUrl($invoice),
                'client_reference_id' => $invoice->invoice_number,
                'customer_email' => $tenant->billing_email ?? $tenant->email,
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $invoice->subscription_id,
                ],
            ]);

            $this->createPaymentRecord($invoice, $session->id, PaymentStatus::PENDING, [
                'checkout_url' => $session->url,
            ]);

            return [
                'success' => true,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ];

        } catch (ApiErrorException $e) {
            $this->log('Checkout creation failed', ['error' => $e->getMessage()], 'error');

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function processPayment(Invoice $invoice, array $paymentData): Payment
    {
        throw new \BadMethodCallException('Stripe uses checkout redirect flow');
    }

    public function verifyPayment(string $paymentReference): array
    {
        try {
            $session = Session::retrieve($paymentReference);

            return [
                'success' => true,
                'status' => $this->mapStatus($session->payment_status),
                'payment_intent' => $session->payment_intent,
            ];
        } catch (ApiErrorException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function handleWebhook(array $payload, array $headers = []): array
    {
        try {
            $signature = $headers['stripe-signature'][0] ?? $headers['stripe-signature'] ?? '';
            $event = Webhook::constructEvent(
                json_encode($payload),
                $signature,
                $this->config['webhook_secret'] ?? ''
            );

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                $payment = Payment::where('gateway_payment_id', $session->id)->first();

                if ($payment && $session->payment_status === 'paid') {
                    $payment->update([
                        'status' => PaymentStatus::COMPLETED,
                        'paid_at' => now(),
                        'gateway_payment_id' => $session->payment_intent ?? $session->id,
                    ]);

                    $payment->invoice->update([
                        'status' => \App\Enums\InvoiceStatus::PAID,
                        'paid_at' => now(),
                    ]);

                    $this->activateSubscription($payment);
                }
            }

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refund(Payment $payment, float $amount, ?string $reason = null): array
    {
        try {
            $refund = Refund::create([
                'payment_intent' => $payment->gateway_payment_id,
                'amount' => $this->formatAmount($amount, $payment->currency),
            ]);

            $refundedAmount = ($payment->metadata['refunded_amount'] ?? 0) + $amount;

            $payment->update([
                'status' => $amount >= $payment->amount ? PaymentStatus::REFUNDED : PaymentStatus::COMPLETED,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refunded_amount' => $refundedAmount,
                    'refunded_at' => now()->toDateTimeString(),
                    'refund_id' => $refund->id,
                ]),
            ]);

            return ['success' => true, 'refund_id' => $refund->id];
        } catch (ApiErrorException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPaymentStatus(string $paymentReference): string
    {
        try {
            $session = Session::retrieve($paymentReference);

            return $this->mapStatus($session->payment_status);
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    protected function mapStatus(string $status): string
    {
        return match ($status) {
            'paid', 'succeeded' => 'completed',
            'unpaid', 'requires_payment_method' => 'pending',
            'canceled' => 'cancelled',
            default => 'pending',
        };
    }
}
