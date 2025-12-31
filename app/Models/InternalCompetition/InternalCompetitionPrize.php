<?php

namespace App\Models\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\PrizeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class InternalCompetitionPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'metric_type',
        'rank',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'image_path',
        'prize_type',
        'estimated_value',
        'currency',
        'physical_details',
    ];

    protected $casts = [
        'metric_type' => CompetitionMetric::class,
        'prize_type' => PrizeType::class,
        'rank' => 'integer',
        'estimated_value' => 'decimal:2',
        'physical_details' => 'array',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(InternalCompetition::class, 'competition_id');
    }

    public function winner(): HasOne
    {
        return $this->hasOne(InternalCompetitionWinner::class, 'prize_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar
            ? $this->name_ar
            : $this->name;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function getRankLabelAttribute(): string
    {
        return match($this->rank) {
            1 => 'المركز الأول 🥇',
            2 => 'المركز الثاني 🥈',
            3 => 'المركز الثالث 🥉',
            default => "المركز {$this->rank}",
        };
    }
}
