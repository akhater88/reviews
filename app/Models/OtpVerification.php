<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'phone',
        'otp_code',
        'attempts',
        'send_count',
        'expires_at',
        'last_sent_at',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Check if the OTP is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if max attempts have been reached.
     */
    public function hasMaxAttempts(int $maxAttempts = 3): bool
    {
        return $this->attempts >= $maxAttempts;
    }

    /**
     * Check if can resend OTP (cooldown period passed).
     */
    public function canResend(int $cooldownSeconds = 30): bool
    {
        if (!$this->last_sent_at) {
            return true;
        }

        return $this->last_sent_at->addSeconds($cooldownSeconds)->isPast();
    }

    /**
     * Get remaining cooldown time in seconds.
     */
    public function getRemainingCooldown(int $cooldownSeconds = 30): int
    {
        if (!$this->last_sent_at) {
            return 0;
        }

        $remaining = $cooldownSeconds - $this->last_sent_at->diffInSeconds(now());

        return max(0, $remaining);
    }
}
