<?php

namespace App\Models\Competition;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionBranch extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'google_rating' => 'decimal:1',
        'photos' => 'array',
        'opening_hours' => 'array',
        'types' => 'array',
        'first_nominated_at' => 'datetime',
        'reviews_last_synced_at' => 'datetime',
        'is_active' => 'boolean',
        'is_eligible' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function firstNominatedBy(): BelongsTo
    {
        return $this->belongsTo(CompetitionParticipant::class, 'first_nominated_by');
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(CompetitionNomination::class, 'competition_branch_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(CompetitionScore::class, 'competition_branch_id');
    }

    public function wins(): HasMany
    {
        return $this->hasMany(CompetitionWinner::class, 'competition_branch_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEligible($query)
    {
        return $query->where('is_eligible', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name_ar ?: $this->name;
    }

    public function getGoogleMapsUrlAttribute(): string
    {
        return "https://www.google.com/maps/place/?q=place_id:{$this->google_place_id}";
    }

    public function getScoreForPeriod(int $periodId): ?CompetitionScore
    {
        return $this->scores()->where('competition_period_id', $periodId)->first();
    }

    public function getNominationsForPeriod(int $periodId): int
    {
        return $this->nominations()->where('competition_period_id', $periodId)->count();
    }

    /**
     * Update the sync status of this branch
     */
    public function updateSyncStatus(string $status, ?string $error = null): void
    {
        $this->update([
            'sync_status' => $status,
            'sync_error' => $error,
        ]);
    }
}
