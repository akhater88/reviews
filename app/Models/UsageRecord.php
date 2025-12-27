<?php

namespace App\Models;

use App\Enums\UsageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'usage_type',
        'quantity',
        'metadata',
        'recorded_at',
    ];

    protected $casts = [
        'usage_type' => UsageType::class,
        'metadata' => 'array',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($record) {
            $record->recorded_at = $record->recorded_at ?? now();
            $record->created_at = now();
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Static helper to record usage.
     */
    public static function record(
        int $tenantId,
        UsageType $type,
        int $quantity = 1,
        ?array $metadata = null
    ): self {
        $record = static::create([
            'tenant_id' => $tenantId,
            'usage_type' => $type,
            'quantity' => $quantity,
            'metadata' => $metadata,
        ]);

        // Also update the summary
        $summary = UsageSummary::getCurrentForTenant($tenantId);
        $summary->incrementUsage($type->summaryField(), $quantity);

        return $record;
    }

    // Scopes
    public function scopeOfType($query, UsageType $type)
    {
        return $query->where('usage_type', $type);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('recorded_at', now()->month)
            ->whereYear('recorded_at', now()->year);
    }

    public function scopeInPeriod($query, int $month, int $year)
    {
        return $query->whereMonth('recorded_at', $month)
            ->whereYear('recorded_at', $year);
    }
}
