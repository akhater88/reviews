<?php

namespace App\Models\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\CompetitionPeriod;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\LeaderboardVisibility;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InternalCompetition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'created_by_id',
        'created_by_type',
        'tenant_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'cover_image',
        'scope',
        'period_type',
        'start_date',
        'end_date',
        'status',
        'metrics_config',
        'leaderboard_visibility',
        'show_progress_hints',
        'public_showcase',
        'notification_settings',
        'activated_at',
        'ended_at',
        'published_at',
    ];

    protected $casts = [
        'scope' => CompetitionScope::class,
        'period_type' => CompetitionPeriod::class,
        'status' => CompetitionStatus::class,
        'leaderboard_visibility' => LeaderboardVisibility::class,
        'metrics_config' => 'array',
        'notification_settings' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'show_progress_hints' => 'boolean',
        'public_showcase' => 'boolean',
        'activated_at' => 'datetime',
        'ended_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function participatingTenants(): HasMany
    {
        return $this->hasMany(InternalCompetitionTenant::class, 'competition_id');
    }

    public function activeTenants(): HasMany
    {
        return $this->participatingTenants()->where('status', 'active');
    }

    public function participatingBranches(): HasMany
    {
        return $this->hasMany(InternalCompetitionBranch::class, 'competition_id');
    }

    public function activeBranches(): HasMany
    {
        return $this->participatingBranches()->where('status', 'active');
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(InternalCompetitionPrize::class, 'competition_id');
    }

    public function branchScores(): HasMany
    {
        return $this->hasMany(InternalCompetitionBranchScore::class, 'competition_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(InternalCompetitionEmployee::class, 'competition_id');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(InternalCompetitionWinner::class, 'competition_id');
    }

    public function benchmarks(): HasMany
    {
        return $this->hasMany(InternalCompetitionBenchmark::class, 'competition_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(InternalCompetitionNotification::class, 'competition_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', CompetitionStatus::ACTIVE);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', CompetitionStatus::DRAFT);
    }

    public function scopeEnded($query)
    {
        return $query->whereIn('status', [CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED]);
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
              ->orWhereHas('participatingTenants', function ($q2) use ($tenantId) {
                  $q2->where('tenant_id', $tenantId)->where('status', 'active');
              });
        });
    }

    public function scopeSingleTenant($query)
    {
        return $query->where('scope', CompetitionScope::SINGLE_TENANT);
    }

    public function scopeMultiTenant($query)
    {
        return $query->where('scope', CompetitionScope::MULTI_TENANT);
    }

    // ==================== ACCESSORS ====================

    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar
            ? $this->name_ar
            : $this->name;
    }

    public function getDurationInDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    public function getRemainingDaysAttribute(): int
    {
        if ($this->status !== CompetitionStatus::ACTIVE) {
            return 0;
        }
        return max(0, now()->diffInDays($this->end_date, false));
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->status !== CompetitionStatus::ACTIVE) {
            return $this->status->isCompleted() ? 100 : 0;
        }

        $total = $this->duration_in_days;
        $elapsed = $this->start_date->diffInDays(now());

        return min(100, round(($elapsed / $total) * 100, 2));
    }

    public function getEnabledMetricsAttribute(): array
    {
        $enabled = [];
        foreach ($this->metrics_config ?? [] as $metric => $config) {
            if ($config['enabled'] ?? false) {
                $enabled[] = CompetitionMetric::tryFrom($metric);
            }
        }
        return array_filter($enabled);
    }

    public function getIsMultiTenantAttribute(): bool
    {
        return $this->scope === CompetitionScope::MULTI_TENANT;
    }

    // ==================== METHODS ====================

    public function isMetricEnabled(CompetitionMetric $metric): bool
    {
        return $this->metrics_config[$metric->value]['enabled'] ?? false;
    }

    public function canBeActivated(): bool
    {
        return $this->status->canActivate()
            && $this->activeBranches()->count() > 0
            && count($this->enabled_metrics) > 0;
    }

    public function shouldShowLeaderboard(): bool
    {
        return match($this->leaderboard_visibility) {
            LeaderboardVisibility::ALWAYS => true,
            LeaderboardVisibility::AFTER_END => $this->status->isCompleted(),
            LeaderboardVisibility::HIDDEN => false,
        };
    }

    public function hasTenantParticipating(int $tenantId): bool
    {
        if (!$this->is_multi_tenant) {
            return $this->tenant_id === $tenantId;
        }
        return $this->activeTenants()->where('tenant_id', $tenantId)->exists();
    }

    public function getProgressHintForBranch(int $branchId, CompetitionMetric $metric): ?string
    {
        if (!$this->show_progress_hints || $this->status !== CompetitionStatus::ACTIVE) {
            return null;
        }

        $avgScore = $metric === CompetitionMetric::EMPLOYEE_MENTIONS
            ? $this->employees()->avg('score')
            : $this->branchScores()->where('metric_type', $metric->value)->avg('score');

        $branchScore = $metric === CompetitionMetric::EMPLOYEE_MENTIONS
            ? $this->employees()->where('branch_id', $branchId)->max('score')
            : $this->branchScores()->where('branch_id', $branchId)->where('metric_type', $metric->value)->value('score');

        if (!$branchScore) {
            return 'تحتاج إلى تحسين لتصل للمراكز الأولى';
        }

        if ($branchScore > ($avgScore ?? 0) * 1.2) {
            return 'أنت في المسار الصحيح! استمر';
        } elseif ($branchScore >= ($avgScore ?? 0)) {
            return 'أداءك أعلى من المتوسط';
        } else {
            return 'تحتاج إلى تحسين لتصل للمراكز الأولى';
        }
    }
}
