<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'google_review_id',
        'reviewer_name',
        'reviewer_photo_url',
        'rating',
        'text',
        'review_date',
        'language',
        'sentiment',
        'sentiment_score',
        'categories',
        'keywords',
        'reviewer_gender',
        'is_replied',
        'needs_reply',
    ];

    protected $casts = [
        'review_date' => 'datetime',
        'categories' => 'array',
        'keywords' => 'array',
        'sentiment_score' => 'decimal:2',
        'is_replied' => 'boolean',
        'needs_reply' => 'boolean',
    ];

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
        return match($this->sentiment) {
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
        return match($this->sentiment) {
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
     * Check if review has text content.
     */
    public function hasText(): bool
    {
        return !empty($this->text);
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
     * Scope for reviews needing reply.
     */
    public function scopeNeedsReply($query)
    {
        return $query->where('needs_reply', true);
    }

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
}
