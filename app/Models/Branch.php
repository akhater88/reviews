<?php

namespace App\Models;

use App\Enums\BranchSource;
use App\Enums\BranchType;
use App\Enums\SyncStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'google_place_id',
        'google_account_id',
        'google_location_id',
        'address',
        'city',
        'country',
        'lat',
        'lng',
        'phone',
        'website',
        'is_active',
        'source',
        'branch_type',
        'linked_branch_id',
        'can_reply',
        'last_synced_at',
        'sync_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'can_reply' => 'boolean',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'last_synced_at' => 'datetime',
        'source' => BranchSource::class,
        'branch_type' => BranchType::class,
        'sync_status' => SyncStatus::class,
    ];

    /**
     * Get the tenant that owns the branch
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the linked branch (for competitor comparison)
     */
    public function linkedBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'linked_branch_id');
    }

    /**
     * Get branches linked to this one (competitors)
     */
    public function linkedCompetitors(): HasMany
    {
        return $this->hasMany(Branch::class, 'linked_branch_id');
    }

    /**
     * Get reviews for this branch
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get analysis results for this branch
     */
    public function analysisResults(): HasMany
    {
        return $this->hasMany(AnalysisResult::class);
    }

    /**
     * Get users assigned to this branch
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user')
            ->withTimestamps();
    }

    /**
     * Check if branch is from Google Business
     */
    public function isFromGoogle(): bool
    {
        return $this->source === BranchSource::GOOGLE_BUSINESS;
    }

    /**
     * Check if branch is manually added
     */
    public function isManual(): bool
    {
        return $this->source === BranchSource::MANUAL;
    }

    /**
     * Check if branch is owned (not competitor)
     */
    public function isOwned(): bool
    {
        return $this->branch_type === BranchType::OWNED;
    }

    /**
     * Check if branch is a competitor
     */
    public function isCompetitor(): bool
    {
        return $this->branch_type === BranchType::COMPETITOR;
    }

    /**
     * Check if reviews can be replied to
     */
    public function canReplyToReviews(): bool
    {
        return $this->can_reply && $this->isFromGoogle();
    }

    /**
     * Get sync status label
     */
    public function getSyncStatusLabelAttribute(): string
    {
        return $this->sync_status?->label() ?? 'غير محدد';
    }

    /**
     * Scope: Only owned branches
     */
    public function scopeOwned($query)
    {
        return $query->where('branch_type', BranchType::OWNED->value);
    }

    /**
     * Scope: Only competitor branches
     */
    public function scopeCompetitors($query)
    {
        return $query->where('branch_type', BranchType::COMPETITOR->value);
    }

    /**
     * Scope: Only Google Business branches
     */
    public function scopeFromGoogle($query)
    {
        return $query->where('source', BranchSource::GOOGLE_BUSINESS->value);
    }

    /**
     * Scope: Only manual branches
     */
    public function scopeManual($query)
    {
        return $query->where('source', BranchSource::MANUAL->value);
    }

    /**
     * Scope: Needs sync (pending or failed)
     */
    public function scopeNeedsSync($query)
    {
        return $query->whereIn('sync_status', [
            SyncStatus::PENDING->value,
            SyncStatus::FAILED->value,
        ]);
    }

    /**
     * Scope: Active branches only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
