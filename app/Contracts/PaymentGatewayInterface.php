<?php

namespace App\Contracts;

use App\Models\Invoice;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Get the gateway identifier
     */
    public function getIdentifier(): string;

    /**
     * Get the gateway display name
     */
    public function getName(): string;

    /**
     * Get the gateway display name in Arabic
     */
    public function getNameAr(): string;

    /**
     * Check if gateway is available for a currency
     */
    public function supportsCurrency(string $currency): bool;

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array;

    /**
     * Create a checkout session/payment intent
     */
    public function createCheckout(Invoice $invoice, array $options = []): array;

    /**
     * Process a payment (for direct payment methods)
     */
    public function processPayment(Invoice $invoice, array $paymentData): Payment;

    /**
     * Verify a payment status
     */
    public function verifyPayment(string $paymentReference): array;

    /**
     * Handle webhook payload
     */
    public function handleWebhook(array $payload, array $headers = []): array;

    /**
     * Refund a payment
     */
    public function refund(Payment $payment, float $amount, ?string $reason = null): array;

    /**
     * Get payment status from gateway
     */
    public function getPaymentStatus(string $paymentReference): string;

    /**
     * Check if gateway requires redirect
     */
    public function requiresRedirect(): bool;

    /**
     * Validate gateway configuration
     */
    public function validateConfiguration(): bool;
}
