<?php

namespace App\Models\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalCompetitionBranchScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'tenant_id',
        'branch_id',
        'metric_type',
        'score',
        'rank',
        'score_breakdown',
        'period_start',
        'period_end',
        'is_final',
        'calculated_at',
    ];

    protected $casts = [
        'metric_type' => CompetitionMetric::class,
        'score' => 'decimal:4',
        'rank' => 'integer',
        'score_breakdown' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'is_final' => 'boolean',
        'calculated_at' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(InternalCompetition::class, 'competition_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeForMetric($query, CompetitionMetric $metric)
    {
        return $query->where('metric_type', $metric);
    }

    public function scopeRanked($query)
    {
        return $query->orderByDesc('score');
    }

    public function getRankLabelAttribute(): ?string
    {
        return match($this->rank) {
            1 => 'المركز الأول 🥇',
            2 => 'المركز الثاني 🥈',
            3 => 'المركز الثالث 🥉',
            default => $this->rank ? "المركز {$this->rank}" : null,
        };
    }
}
