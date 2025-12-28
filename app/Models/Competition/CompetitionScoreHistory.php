<?php

namespace App\Models\Competition;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionScoreHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'competition_score_id',
        'competition_score',
        'rank_position',
        'rating_score',
        'sentiment_score',
        'response_rate',
        'volume_score',
        'trend_score',
        'keyword_score',
        'recorded_at',
    ];

    protected $casts = [
        'competition_score' => 'decimal:2',
        'rating_score' => 'decimal:2',
        'sentiment_score' => 'decimal:2',
        'response_rate' => 'decimal:2',
        'volume_score' => 'decimal:2',
        'trend_score' => 'decimal:2',
        'keyword_score' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function score(): BelongsTo
    {
        return $this->belongsTo(CompetitionScore::class, 'competition_score_id');
    }
}
