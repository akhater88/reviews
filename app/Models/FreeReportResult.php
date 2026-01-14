<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreeReportResult extends Model
{
    protected $fillable = [
        'free_report_id',
        'overall_score',
        'total_reviews',
        'average_rating',
        'sentiment_breakdown',
        'category_scores',
        'top_strengths',
        'top_weaknesses',
        'keyword_analysis',
        'executive_summary',
        'recommendations',
    ];

    protected function casts(): array
    {
        return [
            'overall_score' => 'decimal:1',
            'total_reviews' => 'integer',
            'average_rating' => 'decimal:1',
            'sentiment_breakdown' => 'array',
            'category_scores' => 'array',
            'top_strengths' => 'array',
            'top_weaknesses' => 'array',
            'keyword_analysis' => 'array',
            'recommendations' => 'array',
        ];
    }

    /**
     * Get the free report that owns this result.
     */
    public function freeReport(): BelongsTo
    {
        return $this->belongsTo(FreeReport::class);
    }

    /**
     * Get the performance grade based on overall score.
     */
    public function getGrade(): string
    {
        if ($this->overall_score >= 9) {
            return 'A+';
        } elseif ($this->overall_score >= 8) {
            return 'A';
        } elseif ($this->overall_score >= 7) {
            return 'B';
        } elseif ($this->overall_score >= 6) {
            return 'C';
        } elseif ($this->overall_score >= 5) {
            return 'D';
        }
        return 'F';
    }

    /**
     * Get the grade color for UI display.
     */
    public function getGradeColor(): string
    {
        $grade = $this->getGrade();

        return match($grade) {
            'A+', 'A' => 'green',
            'B' => 'blue',
            'C' => 'yellow',
            'D' => 'orange',
            default => 'red',
        };
    }

    /**
     * Get sentiment percentages.
     */
    public function getSentimentPercentages(): array
    {
        $breakdown = $this->sentiment_breakdown ?? [];
        $total = array_sum($breakdown);

        if ($total === 0) {
            return ['positive' => 0, 'neutral' => 0, 'negative' => 0];
        }

        return [
            'positive' => round(($breakdown['positive'] ?? 0) / $total * 100, 1),
            'neutral' => round(($breakdown['neutral'] ?? 0) / $total * 100, 1),
            'negative' => round(($breakdown['negative'] ?? 0) / $total * 100, 1),
        ];
    }
}
