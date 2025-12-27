<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'invoice_number',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'billing_cycle',
        'period_start',
        'period_end',
        'due_date',
        'paid_at',
        'payment_reference',
        'items',
        'billing_details',
        'notes',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'billing_cycle' => BillingCycle::class,
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'items' => 'array',
        'billing_details' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (! $invoice->invoice_number) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = config('subscription.invoice.prefix', 'INV-');
        $year = now()->format('Y');
        $lastInvoice = static::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -6) + 1 : 1;

        return $prefix.$year.'-'.str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Helpers
    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }

    public function isPending(): bool
    {
        return $this->status === InvoiceStatus::PENDING;
    }

    public function isOverdue(): bool
    {
        return $this->status === InvoiceStatus::PENDING && $this->due_date->isPast();
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => InvoiceStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    public function markAsOverdue(): void
    {
        $this->update(['status' => InvoiceStatus::OVERDUE]);
    }

    public function getFormattedTotal(): string
    {
        $symbol = $this->currency === 'SAR' ? 'ر.س' : '$';

        return $symbol.' '.number_format($this->total_amount, 2);
    }

    public function getFormattedSubtotal(): string
    {
        $symbol = $this->currency === 'SAR' ? 'ر.س' : '$';

        return $symbol.' '.number_format($this->subtotal, 2);
    }

    public function getFormattedTax(): string
    {
        $symbol = $this->currency === 'SAR' ? 'ر.س' : '$';

        return $symbol.' '.number_format($this->tax_amount, 2);
    }

    public function getSuccessfulPayment(): ?Payment
    {
        return $this->payments()->completed()->first();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', InvoiceStatus::PENDING);
    }

    public function scopeOverdue($query)
    {
        return $query->pending()->where('due_date', '<', now());
    }

    public function scopePaid($query)
    {
        return $query->where('status', InvoiceStatus::PAID);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [InvoiceStatus::PENDING, InvoiceStatus::OVERDUE]);
    }
}
