<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    protected $fillable = [
        'plan_id',
        'feature_id',
        'is_enabled',
        'limit_value',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'limit_value' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * Check if this plan feature has a limit.
     */
    public function hasLimit(): bool
    {
        return $this->limit_value !== null;
    }

    /**
     * Check if the limit is unlimited (-1).
     */
    public function isUnlimited(): bool
    {
        return $this->limit_value === -1;
    }
}
