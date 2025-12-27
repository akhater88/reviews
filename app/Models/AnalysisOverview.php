<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use App\Enums\AnalysisType;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AnalysisOverview extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'restaurant_id',
        'status',
        'current_step',
        'progress',
        'error_message',
        'period_start',
        'period_end',
        'total_reviews',
        'reviews_with_text',
        'star_only_reviews',
        'started_at',
        'completed_at',
        'total_processing_time',
        'total_tokens_used',
    ];

    protected $casts = [
        'status' => AnalysisStatus::class,
        'period_start' => 'date',
        'period_end' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(AnalysisResult::class);
    }

    // Individual result relationships
    public function sentimentResult(): HasOne
    {
        return $this->hasOne(AnalysisResult::class)->where('analysis_type', AnalysisType::SENTIMENT);
    }

    public function recommendationsResult(): HasOne
    {
        return $this->hasOne(AnalysisResult::class)->where('analysis_type', AnalysisType::RECOMMENDATIONS);
    }

    public function keywordsResult(): HasOne
    {
        return $this->hasOne(AnalysisResult::class)->where('analysis_type', AnalysisType::KEYWORDS);
    }

    public function operationalResult(): HasOne
    {
        return $this->hasOne(AnalysisResult::class)->where('analysis_type', AnalysisType::OPERATIONAL_INTELLIGENCE);
    }

    public function categoryResult(): HasOne
    {
        return $this->hasOne(AnalysisResult::class)->where('analysis_type', AnalysisType::CATEGORY_INSIGHTS);
    }

    public function employeesResult(): HasOne
    {
        return $this->hasOne(AnalysisResult::class)->where('analysis_type', AnalysisType::EMPLOYEES_INSIGHTS);
    }

    public function genderResult(): HasOne
    {
        return $this->hasOne(AnalysisResult::class)->where('analysis_type', AnalysisType::GENDER_INSIGHTS);
    }

    public function overviewCardsResult(): HasOne
    {
        return $this->hasOne(AnalysisResult::class)->where('analysis_type', AnalysisType::OVERVIEW_CARDS);
    }

    // Helpers
    public function isCompleted(): bool
    {
        return $this->status === AnalysisStatus::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === AnalysisStatus::FAILED;
    }

    public function isProcessing(): bool
    {
        return $this->status === AnalysisStatus::PROCESSING;
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => AnalysisStatus::PROCESSING,
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => AnalysisStatus::COMPLETED,
            'progress' => 100,
            'completed_at' => now(),
            'total_processing_time' => $this->started_at
                ? abs((int) now()->diffInSeconds($this->started_at))
                : null,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => AnalysisStatus::FAILED,
            'error_message' => $error,
            'completed_at' => now(),
        ]);
    }

    public function updateProgress(int $progress, string $step): void
    {
        $this->update([
            'progress' => $progress,
            'current_step' => $step,
        ]);
    }

    public function addTokens(int $tokens): void
    {
        $this->increment('total_tokens_used', $tokens);
    }
}
