<?php

namespace App\Models\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\PrizeStatus;
use App\Enums\InternalCompetition\WinnerType;
use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalCompetitionWinner extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'prize_id',
        'winner_type',
        'tenant_id',
        'branch_id',
        'employee_id',
        'employee_name',
        'metric_type',
        'final_score',
        'final_rank',
        'prize_status',
        'announced_at',
        'claimed_at',
        'delivered_at',
        'delivery_notes',
        'delivery_proof_path',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
    ];

    protected $casts = [
        'winner_type' => WinnerType::class,
        'metric_type' => CompetitionMetric::class,
        'prize_status' => PrizeStatus::class,
        'final_score' => 'decimal:4',
        'final_rank' => 'integer',
        'announced_at' => 'datetime',
        'claimed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(InternalCompetition::class, 'competition_id');
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(InternalCompetitionPrize::class, 'prize_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(InternalCompetitionEmployee::class, 'employee_id');
    }

    public function getWinnerDisplayNameAttribute(): string
    {
        return $this->winner_type === WinnerType::EMPLOYEE
            ? ($this->employee_name ?? 'موظف غير محدد')
            : ($this->branch?->name ?? 'فرع غير محدد');
    }

    public function getRankLabelAttribute(): string
    {
        return match($this->final_rank) {
            1 => 'المركز الأول 🥇',
            2 => 'المركز الثاني 🥈',
            3 => 'المركز الثالث 🥉',
            default => "المركز {$this->final_rank}",
        };
    }

    public function markAsDelivered(?string $proofPath = null, ?string $notes = null): bool
    {
        return $this->update([
            'prize_status' => PrizeStatus::DELIVERED,
            'delivered_at' => now(),
            'delivery_proof_path' => $proofPath,
            'delivery_notes' => $notes,
        ]);
    }
}
