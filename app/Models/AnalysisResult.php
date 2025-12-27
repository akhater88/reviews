<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use App\Enums\AnalysisType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisResult extends Model
{
    protected $fillable = [
        'analysis_overview_id',
        'restaurant_id',
        'analysis_type',
        'result',
        'status',
        'provider',
        'model',
        'processing_time',
        'tokens_used',
        'confidence',
        'review_count',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'analysis_type' => AnalysisType::class,
        'status' => AnalysisStatus::class,
        'result' => 'array',
        'confidence' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    // Relationships
    public function analysisOverview(): BelongsTo
    {
        return $this->belongsTo(AnalysisOverview::class);
    }

    // Helpers
    public function getResultAttribute($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }
}
