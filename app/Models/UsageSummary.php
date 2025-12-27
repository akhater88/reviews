<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageSummary extends Model
{
    protected $fillable = [
        'tenant_id',
        'period_month',
        'period_year',
        'ai_replies_used',
        'ai_tokens_used',
        'api_calls_used',
        'reviews_synced',
        'analysis_runs',
        'branches_count',
        'competitors_count',
        'users_count',
    ];

    protected $casts = [
        'period_month' => 'integer',
        'period_year' => 'integer',
        'ai_replies_used' => 'integer',
        'ai_tokens_used' => 'integer',
        'api_calls_used' => 'integer',
        'reviews_synced' => 'integer',
        'analysis_runs' => 'integer',
        'branches_count' => 'integer',
        'competitors_count' => 'integer',
        'users_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get or create current month's summary.
     */
    public static function getCurrentForTenant(int $tenantId): self
    {
        return static::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'period_month' => now()->month,
                'period_year' => now()->year,
            ],
            [
                'ai_replies_used' => 0,
                'ai_tokens_used' => 0,
                'api_calls_used' => 0,
                'reviews_synced' => 0,
                'analysis_runs' => 0,
            ]
        );
    }

    /**
     * Get summary for specific month/year.
     */
    public static function getForPeriod(int $tenantId, int $month, int $year): ?self
    {
        return static::where('tenant_id', $tenantId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();
    }

    /**
     * Increment usage counter.
     */
    public function incrementUsage(string $field, int $amount = 1): void
    {
        $this->increment($field, $amount);
    }

    /**
     * Check if usage is within limit.
     */
    public function isWithinLimit(string $usageField, int $limit): bool
    {
        if ($limit === -1) {
            return true;
        } // Unlimited

        return $this->{$usageField} < $limit;
    }

    /**
     * Get remaining quota.
     */
    public function getRemainingQuota(string $usageField, int $limit): int
    {
        if ($limit === -1) {
            return -1;
        } // Unlimited

        return max(0, $limit - $this->{$usageField});
    }

    /**
     * Get usage percentage.
     */
    public function getUsagePercentage(string $usageField, int $limit): float
    {
        if ($limit === -1) {
            return 0;
        } // Unlimited
        if ($limit === 0) {
            return 100;
        }

        return min(100, ($this->{$usageField} / $limit) * 100);
    }

    /**
     * Check if approaching limit (based on config warning percentage).
     */
    public function isApproachingLimit(string $usageField, int $limit): bool
    {
        $warningPercentage = config('subscription.notifications.usage_warning_percentage', 80);

        return $this->getUsagePercentage($usageField, $limit) >= $warningPercentage;
    }

    /**
     * Update resource counts snapshot.
     */
    public function updateResourceCounts(int $branches, int $competitors, int $users): void
    {
        $this->update([
            'branches_count' => $branches,
            'competitors_count' => $competitors,
            'users_count' => $users,
        ]);
    }

    /**
     * Get period display string.
     */
    public function getPeriodDisplayAttribute(): string
    {
        $months = [
            1 => 'يناير',
            2 => 'فبراير',
            3 => 'مارس',
            4 => 'أبريل',
            5 => 'مايو',
            6 => 'يونيو',
            7 => 'يوليو',
            8 => 'أغسطس',
            9 => 'سبتمبر',
            10 => 'أكتوبر',
            11 => 'نوفمبر',
            12 => 'ديسمبر',
        ];

        return $months[$this->period_month].' '.$this->period_year;
    }
}
