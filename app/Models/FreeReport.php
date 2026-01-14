<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class FreeReport extends Model
{
    protected $fillable = [
        'phone',
        'place_id',
        'business_name',
        'business_address',
        'magic_link_token',
        'magic_link_expires_at',
        'magic_link_sent_at',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'magic_link_expires_at' => 'datetime',
            'magic_link_sent_at' => 'datetime',
        ];
    }

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_FETCHING_REVIEWS = 'fetching_reviews';
    public const STATUS_ANALYZING = 'analyzing';
    public const STATUS_GENERATING_RESULTS = 'generating_results';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Get the reviews for this free report.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(FreeReportReview::class);
    }

    /**
     * Get the results for this free report.
     */
    public function result(): HasOne
    {
        return $this->hasOne(FreeReportResult::class);
    }

    /**
     * Generate a new magic link token.
     */
    public function generateMagicLinkToken(): string
    {
        $token = Str::random(64);

        $this->update([
            'magic_link_token' => $token,
            'magic_link_expires_at' => now()->addHours(24),
        ]);

        return $token;
    }

    /**
     * Check if the magic link is valid.
     */
    public function isMagicLinkValid(): bool
    {
        return $this->magic_link_token
            && $this->magic_link_expires_at
            && $this->magic_link_expires_at->isFuture();
    }

    /**
     * Get the magic link URL.
     */
    public function getMagicLinkUrl(): ?string
    {
        if (!$this->magic_link_token) {
            return null;
        }

        return url("/free-report/{$this->magic_link_token}");
    }

    /**
     * Check if the report is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the report has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the report is processing.
     */
    public function isProcessing(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_FETCHING_REVIEWS,
            self::STATUS_ANALYZING,
            self::STATUS_GENERATING_RESULTS,
        ]);
    }

    /**
     * Update status with optional error message.
     */
    public function updateStatus(string $status, ?string $errorMessage = null): void
    {
        $data = ['status' => $status];

        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }

        $this->update($data);
    }

    /**
     * Find report by magic link token.
     */
    public static function findByMagicToken(string $token): ?self
    {
        return static::where('magic_link_token', $token)
            ->where('magic_link_expires_at', '>', now())
            ->first();
    }
}
