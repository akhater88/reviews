<?php

namespace App\Models\Competition;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionParticipant extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'whatsapp_opted_in' => 'boolean',
        'sms_opted_in' => 'boolean',
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
    ];

    protected $hidden = ['verification_code'];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by_id');
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(CompetitionNomination::class, 'participant_id');
    }

    public function wins(): HasMany
    {
        return $this->hasMany(CompetitionWinner::class, 'participant_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_blocked', false);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('phone_verified_at');
    }

    public function isVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function canNominate(): bool
    {
        return $this->is_active && !$this->is_blocked && $this->isVerified();
    }

    public function hasNominatedInPeriod(int $periodId): bool
    {
        return $this->nominations()->where('competition_period_id', $periodId)->exists();
    }

    public function getMaskedPhoneAttribute(): string
    {
        return substr($this->phone, 0, 4) . '****' . substr($this->phone, -2);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? $this->masked_phone;
    }
}
