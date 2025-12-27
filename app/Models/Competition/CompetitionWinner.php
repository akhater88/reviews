<?php

namespace App\Models\Competition;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionWinner extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'prize_amount' => 'decimal:2',
        'prize_details' => 'array',
        'prize_claimed' => 'boolean',
        'prize_claimed_at' => 'datetime',
        'claim_details' => 'array',
        'is_notified' => 'boolean',
        'notified_at' => 'datetime',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(CompetitionPeriod::class, 'competition_period_id');
    }

    public function competitionBranch(): BelongsTo
    {
        return $this->belongsTo(CompetitionBranch::class, 'competition_branch_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(CompetitionParticipant::class, 'participant_id');
    }

    public function nomination(): BelongsTo
    {
        return $this->belongsTo(CompetitionNomination::class, 'nomination_id');
    }

    public function scopeClaimed($query)
    {
        return $query->where('prize_claimed', true);
    }

    public function scopeUnclaimed($query)
    {
        return $query->where('prize_claimed', false);
    }

    public function getPrizeDisplayAttribute(): string
    {
        return $this->prize_amount
            ? number_format($this->prize_amount, 0) . ' ' . $this->prize_currency
            : 'جائزة';
    }

    public function getRankLabelAttribute(): string
    {
        return match ($this->prize_rank) {
            1 => 'المركز الأول',
            2 => 'المركز الثاني',
            3 => 'المركز الثالث',
            default => "المركز {$this->prize_rank}",
        };
    }

    public function markAsNotified(string $method): void
    {
        $this->update([
            'is_notified' => true,
            'notified_at' => now(),
            'notification_method' => $method,
        ]);
    }

    public function claim(string $method, array $details = []): void
    {
        $this->update([
            'prize_claimed' => true,
            'prize_claimed_at' => now(),
            'claim_method' => $method,
            'claim_details' => $details,
        ]);
    }
}
