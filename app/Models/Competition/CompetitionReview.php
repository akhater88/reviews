<?php

namespace App\Models\Competition;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionReview extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'rating' => 'integer',
        'review_date' => 'datetime',
        'review_likes' => 'integer',
        'has_owner_response' => 'boolean',
        'owner_response_date' => 'datetime',
        'review_photos' => 'array',
        'sentiment_score' => 'decimal:2',
        'keywords' => 'array',
        'categories' => 'array',
        'analyzed_at' => 'datetime',
    ];

    public function competitionBranch(): BelongsTo
    {
        return $this->belongsTo(CompetitionBranch::class, 'competition_branch_id');
    }

    public function scopeAnalyzed($query)
    {
        return $query->whereNotNull('analyzed_at');
    }

    public function scopeUnanalyzed($query)
    {
        return $query->whereNull('sentiment_score');
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('competition_branch_id', $branchId);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('review_date', [$startDate, $endDate]);
    }

    public function isPositive(): bool
    {
        return $this->sentiment_label === 'positive' || ($this->sentiment_score && $this->sentiment_score >= 60);
    }

    public function isNegative(): bool
    {
        return $this->sentiment_label === 'negative' || ($this->sentiment_score && $this->sentiment_score < 40);
    }

    public function isNeutral(): bool
    {
        return $this->sentiment_label === 'neutral' ||
            ($this->sentiment_score && $this->sentiment_score >= 40 && $this->sentiment_score < 60);
    }
}
