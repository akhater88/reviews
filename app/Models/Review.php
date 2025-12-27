<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Review extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'google_review_id',
        'outscraper_review_id',
        'reviewer_name',
        'reviewer_photo_url',
        'author_url',
        'rating',
        'text',
        'language',
        'review_date',
        'collected_at',
        'source',
        'owner_reply',
        'owner_reply_date',
        'replied_via_tabsense',
        'sentiment',
        'sentiment_score',
        'categories',
        'keywords',
        'ai_summary',
        'reviewer_gender',
        'quality_score',
        'is_spam',
        'is_hidden',
        'is_replied',
        'needs_reply',
        'metadata',
    ];

    protected $casts = [
        'review_date' => 'datetime',
        'collected_at' => 'datetime',
        'owner_reply_date' => 'datetime',
        'replied_via_tabsense' => 'boolean',
        'sentiment_score' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'is_spam' => 'boolean',
        'is_hidden' => 'boolean',
        'is_replied' => 'boolean',
        'needs_reply' => 'boolean',
        'categories' => 'array',
        'keywords' => 'array',
        'metadata' => 'array',
    ];

    // Relationships

    /**
     * Get the tenant that owns this review.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the branch this review belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all replies for this review.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ReviewReply::class);
    }

    /**
     * Get the latest reply.
     */
    public function latestReply(): HasOne
    {
        return $this->hasOne(ReviewReply::class)->latestOfMany();
    }

    /**
     * Get the published reply.
     */
    public function publishedReply(): HasOne
    {
        return $this->hasOne(ReviewReply::class)->where('is_published', true);
    }

    // Scopes

    /**
     * Scope for positive reviews.
     */
    public function scopePositive($query)
    {
        return $query->where('sentiment', 'positive');
    }

    /**
     * Scope for negative reviews.
     */
    public function scopeNegative($query)
    {
        return $query->where('sentiment', 'negative');
    }

    /**
     * Scope for neutral reviews.
     */
    public function scopeNeutral($query)
    {
        return $query->where('sentiment', 'neutral');
    }

    /**
     * Scope for reviews with text.
     */
    public function scopeWithText($query)
    {
        return $query->whereNotNull('text')->where('text', '!=', '');
    }

    /**
     * Scope for star-only reviews (no text).
     */
    public function scopeStarOnly($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('text')->orWhere('text', '');
        });
    }

    /**
     * Scope for unreplied reviews.
     */
    public function scopeUnreplied($query)
    {
        return $query->whereNull('owner_reply');
    }

    /**
     * Scope for reviews from Outscraper.
     */
    public function scopeFromOutscraper($query)
    {
        return $query->where('source', 'outscraper');
    }

    /**
     * Scope for reviews from Google Business.
     */
    public function scopeFromGoogle($query)
    {
        return $query->where('source', 'google_business');
    }

    /**
     * Scope for reviews needing reply.
     */
    public function scopeNeedsReply($query)
    {
        return $query->where('needs_reply', true);
    }

    /**
     * Scope by rating.
     */
    public function scopeWithRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope for recent reviews (last 30 days).
     */
    public function scopeRecent($query)
    {
        return $query->where('review_date', '>=', now()->subDays(30));
    }

    /**
     * Scope for visible reviews (not hidden or spam).
     */
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false)->where('is_spam', false);
    }

    // Helpers

    /**
     * Check if review has text content.
     */
    public function hasText(): bool
    {
        return !empty($this->text);
    }

    /**
     * Check if review has owner reply.
     */
    public function hasOwnerReply(): bool
    {
        return !empty($this->owner_reply);
    }

    /**
     * Check if review is positive (4-5 stars).
     */
    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    /**
     * Check if review is negative (1-2 stars).
     */
    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }

    /**
     * Mark as replied.
     */
    public function markAsReplied(): void
    {
        $this->update([
            'is_replied' => true,
            'needs_reply' => false,
        ]);
    }

    /**
     * Get reviewer initials for avatar.
     */
    public function getReviewerInitialsAttribute(): string
    {
        $words = explode(' ', $this->reviewer_name);
        $initials = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_substr($word, 0, 1);
        }

        return mb_strtoupper($initials);
    }

    /**
     * Get sentiment display in Arabic.
     */
    public function getSentimentDisplayAttribute(): string
    {
        return match ($this->sentiment) {
            'positive' => 'إيجابي',
            'neutral' => 'محايد',
            'negative' => 'سلبي',
            default => 'غير محدد',
        };
    }

    /**
     * Get sentiment color for UI.
     */
    public function getSentimentColorAttribute(): string
    {
        return match ($this->sentiment) {
            'positive' => 'success',
            'neutral' => 'warning',
            'negative' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get rating stars display.
     */
    public function getRatingStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Get source display in Arabic.
     */
    public function getSourceDisplayAttribute(): string
    {
        // Hide the actual data source from users - show generic message
        return 'مراجعات Google';
    }
}
