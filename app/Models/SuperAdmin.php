<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class SuperAdmin extends Authenticatable implements FilamentUser, HasAvatar
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $panel->getId() === 'super-admin';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        // Return gravatar as fallback
        $hash = md5(strtolower(trim($this->email)));

        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get the tenant overrides granted by this admin.
     */
    public function grantedOverrides()
    {
        return $this->hasMany(TenantOverride::class, 'granted_by');
    }

    /**
     * Grant a tenant override.
     */
    public function grantOverride(
        int $tenantId,
        string $type,
        string $key,
        string $value,
        ?\DateTime $expiresAt = null,
        ?string $reason = null
    ): TenantOverride {
        return TenantOverride::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'override_type' => $type,
                'key' => $key,
            ],
            [
                'value' => $value,
                'expires_at' => $expiresAt,
                'granted_by' => $this->id,
                'reason' => $reason,
            ]
        );
    }
}
