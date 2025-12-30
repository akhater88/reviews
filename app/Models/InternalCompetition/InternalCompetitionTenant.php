<?php

namespace App\Models\InternalCompetition;

use App\Enums\InternalCompetition\ParticipantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalCompetitionTenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'tenant_id',
        'enrolled_at',
        'enrolled_by_id',
        'status',
        'withdrawn_at',
        'withdrawal_reason',
    ];

    protected $casts = [
        'status' => ParticipantStatus::class,
        'enrolled_at' => 'datetime',
        'withdrawn_at' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(InternalCompetition::class, 'competition_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', ParticipantStatus::ACTIVE);
    }

    public function withdraw(?string $reason = null): bool
    {
        return $this->update([
            'status' => ParticipantStatus::WITHDRAWN,
            'withdrawn_at' => now(),
            'withdrawal_reason' => $reason,
        ]);
    }
}
