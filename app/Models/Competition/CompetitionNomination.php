<?php

namespace App\Models\Competition;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CompetitionNomination extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'nominated_at' => 'datetime',
        'is_valid' => 'boolean',
        'invalidated_at' => 'datetime',
        'is_winner' => 'boolean',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(CompetitionPeriod::class, 'competition_period_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(CompetitionParticipant::class, 'participant_id');
    }

    public function competitionBranch(): BelongsTo
    {
        return $this->belongsTo(CompetitionBranch::class, 'competition_branch_id');
    }

    public function winnerRecord(): HasOne
    {
        return $this->hasOne(CompetitionWinner::class, 'nomination_id');
    }

    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('competition_period_id', $periodId);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('competition_branch_id', $branchId);
    }

    public function invalidate(string $reason, ?int $adminId = null): void
    {
        $this->update([
            'is_valid' => false,
            'invalidation_reason' => $reason,
            'invalidated_at' => now(),
            'invalidated_by' => $adminId,
        ]);
    }

    public function markAsWinner(int $prizeRank): void
    {
        $this->update(['is_winner' => true, 'prize_rank' => $prizeRank]);
    }
}
