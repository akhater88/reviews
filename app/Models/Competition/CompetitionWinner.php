<?php

namespace App\Models\Competition;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionWinner extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'prize_amount' => 'decimal:2',
        'competition_score' => 'decimal:2',
        'prize_details' => 'array',
        'prize_claimed' => 'boolean',
        'prize_claimed_at' => 'datetime',
        'claim_details' => 'array',
        'is_notified' => 'boolean',
        'notified_at' => 'datetime',
        'notification_channels' => 'array',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'selected_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(CompetitionPeriod::class, 'competition_period_id');
    }

    public function competitionBranch(): BelongsTo
    {
        return $this->belongsTo(CompetitionBranch::class, 'competition_branch_id');
    }

    public function score(): BelongsTo
    {
        return $this->belongsTo(CompetitionScore::class, 'competition_score_id');
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

    public function scopeBranchWinners($query)
    {
        return $query->where('winner_type', 'branch');
    }

    public function scopeLotteryWinners($query)
    {
        return $query->where('winner_type', 'lottery');
    }

    public function scopeWithPrize($query)
    {
        return $query->whereNotNull('prize_amount')->where('prize_amount', '>', 0);
    }

    public function getPrizeDisplayAttribute(): string
    {
        return $this->prize_amount
            ? number_format($this->prize_amount, 0) . ' ' . ($this->prize_currency ?? 'SAR')
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

    public function getClaimUrlAttribute(): string
    {
        return route('competition.claim', ['code' => $this->claim_code]);
    }

    public function getDaysToClaimAttribute(): int
    {
        if (!$this->selected_at) {
            return 30;
        }

        return max(0, now()->diffInDays($this->selected_at->addDays(30), false));
    }

    public function canClaim(): bool
    {
        if ($this->prize_claimed) {
            return false;
        }

        if (!$this->selected_at) {
            return true;
        }

        // 30 days to claim
        return $this->selected_at->addDays(30)->isFuture();
    }

    public function isLotteryWinner(): bool
    {
        return $this->winner_type === 'lottery';
    }

    public function isBranchWinner(): bool
    {
        return $this->winner_type === 'branch';
    }

    public function markAsNotified(string $method = 'auto', array $channels = []): void
    {
        $this->update([
            'is_notified' => true,
            'notified_at' => now(),
            'notification_method' => $method,
            'notification_channels' => $channels,
        ]);
    }

    public function claim(string $method = 'bank_transfer', array $details = []): void
    {
        $this->update([
            'prize_claimed' => true,
            'prize_claimed_at' => now(),
            'claim_method' => $method,
            'claim_details' => $details,
        ]);
    }

    public function generateClaimCode(): string
    {
        $code = strtoupper('WIN-' . substr(md5($this->id . now()->timestamp), 0, 8));
        $this->update(['claim_code' => $code]);

        return $code;
    }

    public static function generateLotteryNumber(): string
    {
        return strtoupper('LT-' . now()->format('Ymd') . '-' . substr(uniqid(), -6));
    }
}
