<?php

namespace App\Models\InternalCompetition;

use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalCompetitionEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'tenant_id',
        'branch_id',
        'employee_name',
        'normalized_name',
        'total_mentions',
        'positive_mentions',
        'negative_mentions',
        'neutral_mentions',
        'score',
        'rank',
        'sample_positive_mentions',
        'sample_negative_mentions',
        'first_mention_at',
        'last_mention_at',
        'is_final',
        'calculated_at',
    ];

    protected $casts = [
        'total_mentions' => 'integer',
        'positive_mentions' => 'integer',
        'negative_mentions' => 'integer',
        'neutral_mentions' => 'integer',
        'score' => 'decimal:4',
        'rank' => 'integer',
        'sample_positive_mentions' => 'array',
        'sample_negative_mentions' => 'array',
        'first_mention_at' => 'date',
        'last_mention_at' => 'date',
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

    public function scopeRanked($query)
    {
        return $query->orderByDesc('score');
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Normalize Arabic/English name for fuzzy matching
     */
    public static function normalizeName(string $name): string
    {
        $normalized = mb_strtolower(trim($name));

        // Remove Arabic prefixes
        $normalized = preg_replace('/^(ال|أ|إ)/', '', $normalized);

        // Normalize Arabic characters
        $arabicNormalizations = [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا',
            'ة' => 'ه', 'ى' => 'ي',
            'ؤ' => 'و', 'ئ' => 'ي',
        ];
        $normalized = str_replace(
            array_keys($arabicNormalizations),
            array_values($arabicNormalizations),
            $normalized
        );

        // Remove diacritics (tashkeel)
        $normalized = preg_replace('/[\x{064B}-\x{065F}]/u', '', $normalized);

        return trim(preg_replace('/\s+/', ' ', $normalized));
    }

    /**
     * Calculate score: (positive × 10) + (neutral × 1) - (negative × 5)
     */
    public function calculateScore(): float
    {
        return ($this->positive_mentions * 10)
             + ($this->neutral_mentions * 1)
             - ($this->negative_mentions * 5);
    }

    public function recalculateScore(): bool
    {
        return $this->update([
            'score' => $this->calculateScore(),
            'calculated_at' => now(),
        ]);
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

    public function getPositivePercentageAttribute(): float
    {
        return $this->total_mentions > 0
            ? round(($this->positive_mentions / $this->total_mentions) * 100, 2)
            : 0;
    }
}
