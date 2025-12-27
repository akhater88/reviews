<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'tenant_id',
        'amount',
        'currency',
        'payment_gateway',
        'gateway_payment_id',
        'gateway_customer_id',
        'payment_method',
        'payment_method_details',
        'status',
        'paid_at',
        'failed_at',
        'failure_reason',
        'metadata',
    ];

    protected $casts = [
        'payment_gateway' => PaymentGateway::class,
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Helpers
    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === PaymentStatus::PROCESSING;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

    public function isRefunded(): bool
    {
        return $this->status === PaymentStatus::REFUNDED;
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => PaymentStatus::COMPLETED,
            'paid_at' => now(),
        ]);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => PaymentStatus::PROCESSING]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => PaymentStatus::FAILED,
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    public function markAsRefunded(): void
    {
        $this->update(['status' => PaymentStatus::REFUNDED]);
    }

    public function getFormattedAmount(): string
    {
        $symbol = $this->currency === 'SAR' ? 'ر.س' : '$';

        return $symbol.' '.number_format($this->amount, 2);
    }

    /**
     * Get a masked version of the payment method details.
     */
    public function getMaskedPaymentMethod(): string
    {
        if (! $this->payment_method_details) {
            return $this->payment_method ?? 'غير محدد';
        }

        return $this->payment_method.' - '.$this->payment_method_details;
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', PaymentStatus::COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatus::FAILED);
    }

    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::PENDING);
    }

    public function scopeByGateway($query, PaymentGateway $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }
}
