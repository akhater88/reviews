<?php

namespace App\Models\Competition;

use App\Enums\CompetitionPeriodStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionPeriod extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'analysis_started_at' => 'datetime',
        'analysis_completed_at' => 'datetime',
        'winners_announced_at' => 'datetime',
        'winners_selected' => 'boolean',
        'winners_selected_at' => 'datetime',
        'winners_announced' => 'boolean',
        'status' => CompetitionPeriodStatus::class,
        'prizes' => 'array',
        'score_weights' => 'array',
        'settings' => 'array',
        'winning_score' => 'decimal:2',
        'first_prize' => 'decimal:2',
        'second_prize' => 'decimal:2',
        'third_prize' => 'decimal:2',
        'nominator_prize' => 'decimal:2',
    ];

    public function winningBranch(): BelongsTo
    {
        return $this->belongsTo(CompetitionBranch::class, 'winning_branch_id');
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(CompetitionNomination::class, 'competition_period_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(CompetitionScore::class, 'competition_period_id');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(CompetitionWinner::class, 'competition_period_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', CompetitionPeriodStatus::ACTIVE);
    }

    public function scopeCurrent($query)
    {
        return $query->where('starts_at', '<=', now())->where('ends_at', '>=', now());
    }

    public static function current(): ?self
    {
        return self::active()->current()->first();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === CompetitionPeriodStatus::ACTIVE;
    }

    public function getIsEndedAttribute(): bool
    {
        return $this->ends_at < now();
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, now()->diffInDays($this->ends_at, false));
    }

    public function getTimeRemainingAttribute(): array
    {
        if ($this->is_ended) {
            return ['days' => 0, 'hours' => 0, 'minutes' => 0];
        }
        $diff = now()->diff($this->ends_at);

        return ['days' => $diff->days, 'hours' => $diff->h, 'minutes' => $diff->i];
    }

    public function canAcceptNominations(): bool
    {
        return $this->status === CompetitionPeriodStatus::ACTIVE && !$this->is_ended;
    }

    public function getScoreWeight(string $component): float
    {
        $defaults = [
            'rating' => 25,
            'sentiment' => 30,
            'response_rate' => 15,
            'volume' => 10,
            'trend' => 10,
            'keywords' => 10,
        ];

        return (($this->score_weights ?? [])[$component] ?? $defaults[$component]) / 100;
    }

    public function getTopBranches(int $limit = 10)
    {
        return $this->scores()
            ->with('competitionBranch')
            ->whereNotNull('rank_position')
            ->orderBy('rank_position')
            ->limit($limit)
            ->get();
    }

    public function recalculateStats(): void
    {
        $this->update([
            'total_nominations' => $this->nominations()->where('is_valid', true)->count(),
            'total_participants' => $this->nominations()->where('is_valid', true)->distinct('participant_id')->count('participant_id'),
            'total_branches' => $this->nominations()->where('is_valid', true)->distinct('competition_branch_id')->count('competition_branch_id'),
        ]);
    }
}
