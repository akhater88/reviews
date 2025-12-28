<?php

namespace App\Models\Competition;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionScore extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'overall_rating' => 'decimal:2',
        'rating_score' => 'decimal:2',
        'sentiment_score' => 'decimal:2',
        'response_rate' => 'decimal:2',
        'review_volume_score' => 'decimal:2',
        'trend_score' => 'decimal:2',
        'keyword_score' => 'decimal:2',
        'positive_ratio' => 'decimal:2',
        'negative_ratio' => 'decimal:2',
        'competition_score' => 'decimal:2',
        'last_analyzed_at' => 'datetime',
        'analysis_details' => 'array',
        'score_history' => 'array',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(CompetitionPeriod::class, 'competition_period_id');
    }

    public function competitionBranch(): BelongsTo
    {
        return $this->belongsTo(CompetitionBranch::class, 'competition_branch_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(CompetitionScoreHistory::class, 'competition_score_id');
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('competition_period_id', $periodId);
    }

    public function scopeRanked($query)
    {
        return $query->whereNotNull('rank_position')->orderBy('rank_position');
    }

    public function calculateScore(?array $weights = null): float
    {
        $period = $this->period;
        $weights = $weights ?? [
            'rating' => $period->getScoreWeight('rating'),
            'sentiment' => $period->getScoreWeight('sentiment'),
            'response_rate' => $period->getScoreWeight('response_rate'),
            'volume' => $period->getScoreWeight('volume'),
            'trend' => $period->getScoreWeight('trend'),
            'keywords' => $period->getScoreWeight('keywords'),
        ];

        $normalizedRating = ($this->overall_rating / 5) * 100;

        return round(
            ($normalizedRating * $weights['rating']) +
            ($this->sentiment_score * $weights['sentiment']) +
            ($this->response_rate * $weights['response_rate']) +
            ($this->review_volume_score * $weights['volume']) +
            ($this->trend_score * $weights['trend']) +
            ($this->keyword_score * $weights['keywords']),
            2
        );
    }

    public function updateScore(): void
    {
        $this->update([
            'competition_score' => $this->calculateScore(),
            'last_analyzed_at' => now(),
        ]);
    }

    public function getPointsToFirstPlace(): float
    {
        if ($this->rank_position === 1) {
            return 0;
        }
        $first = self::forPeriod($this->competition_period_id)->where('rank_position', 1)->first();

        return $first ? round($first->competition_score - $this->competition_score, 2) : 0;
    }

    public function updateNominationCount(): void
    {
        $this->update([
            'nomination_count' => CompetitionNomination::where('competition_period_id', $this->competition_period_id)
                ->where('competition_branch_id', $this->competition_branch_id)
                ->valid()
                ->count(),
        ]);
    }
}
