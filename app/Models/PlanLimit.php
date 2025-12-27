<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanLimit extends Model
{
    protected $fillable = [
        'plan_id',
        'max_branches',
        'max_competitors',
        'max_users',
        'max_reviews_sync',
        'max_ai_replies',
        'max_ai_tokens',
        'max_api_calls',
        'max_analysis_runs',
        'analysis_retention_days',
    ];

    protected $casts = [
        'max_branches' => 'integer',
        'max_competitors' => 'integer',
        'max_users' => 'integer',
        'max_reviews_sync' => 'integer',
        'max_ai_replies' => 'integer',
        'max_ai_tokens' => 'integer',
        'max_api_calls' => 'integer',
        'max_analysis_runs' => 'integer',
        'analysis_retention_days' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isUnlimited(string $key): bool
    {
        return ($this->{$key} ?? 0) === -1;
    }

    public function getFormattedLimit(string $key): string
    {
        $value = $this->{$key} ?? 0;

        if ($value === -1) {
            return 'غير محدود';
        }

        return number_format($value);
    }

    /**
     * Get all limit keys for iteration.
     */
    public static function getLimitKeys(): array
    {
        return [
            'max_branches',
            'max_competitors',
            'max_users',
            'max_reviews_sync',
            'max_ai_replies',
            'max_ai_tokens',
            'max_api_calls',
            'max_analysis_runs',
            'analysis_retention_days',
        ];
    }
}
