<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'phone_verified_at',
        'password',
        'role',
        'login_type',
        'is_active',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model - NO tenant scope on User model itself
     */
    protected static function booted(): void
    {
        // Don't apply TenantScope to User model to avoid infinite loop
        // Users are filtered by their own tenant_id field
    }

    /**
     * Get the tenant this user belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the branches assigned to this user.
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user')
            ->withTimestamps();
    }

    /**
     * Get replies created by this user.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ReviewReply::class);
    }

    /**
     * Check if user is main admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is branch manager.
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user can access a specific branch.
     */
    public function canAccessBranch(Branch $branch): bool
    {
        if ($this->isAdmin()) {
            return $branch->tenant_id === $this->tenant_id;
        }

        return $this->branches()->where('branches.id', $branch->id)->exists();
    }

    /**
     * Get accessible branches for this user.
     */
    public function accessibleBranches()
    {
        if ($this->isAdmin()) {
            return Branch::where('tenant_id', $this->tenant_id);
        }

        return $this->branches();
    }

    /**
     * Filament: Can access panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->tenant && $this->tenant->is_active;
    }

    /**
     * Get the user's display role in Arabic.
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'admin' => 'مدير رئيسي',
            'manager' => 'مدير فرع',
            default => $this->role,
        };
    }

    /**
     * Get user initials for avatar.
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_substr($word, 0, 1);
        }

        return mb_strtoupper($initials);
    }

    /**
     * Check if user's phone is verified.
     */
    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    /**
     * Get masked phone number for display.
     * Example: +966 5** *** *89
     */
    public function getMaskedPhoneAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        $phone = $this->phone;
        $length = strlen($phone);

        if ($length < 8) {
            return $phone;
        }

        // Show first 4 chars (country code) and last 2 chars, mask the rest
        $prefix = substr($phone, 0, 4);
        $suffix = substr($phone, -2);
        $masked = str_repeat('*', $length - 6);

        // Format with spaces for readability
        return $prefix . ' ' . substr($masked, 0, 3) . ' ' . substr($masked, 3) . ' ' . $suffix;
    }
}
