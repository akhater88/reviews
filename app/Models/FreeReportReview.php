<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreeReportReview extends Model
{
    protected $fillable = [
        'free_report_id',
        'review_id',
        'author_name',
        'author_image',
        'rating',
        'text',
        'review_time',
        'language',
        'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'review_time' => 'datetime',
            'raw_data' => 'array',
        ];
    }

    /**
     * Get the free report that owns this review.
     */
    public function freeReport(): BelongsTo
    {
        return $this->belongsTo(FreeReport::class);
    }

    /**
     * Check if the review is positive (4-5 stars).
     */
    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    /**
     * Check if the review is negative (1-2 stars).
     */
    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }

    /**
     * Check if the review is neutral (3 stars).
     */
    public function isNeutral(): bool
    {
        return $this->rating === 3;
    }

    /**
     * Check if the review has text content.
     */
    public function hasText(): bool
    {
        return !empty($this->text);
    }
}
