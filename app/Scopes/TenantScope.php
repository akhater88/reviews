<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * This scope ensures all queries on tenant-scoped models are automatically
     * filtered to only return data belonging to the authenticated user's tenant.
     * This is critical for SaaS multi-tenancy security.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = $this->resolveTenantId();

        if ($tenantId) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }

    /**
     * Resolve the current tenant ID from multiple sources.
     *
     * Priority:
     * 1. Authenticated user's tenant_id (most reliable)
     * 2. Session tenant_id (fallback for edge cases)
     */
    protected function resolveTenantId(): ?int
    {
        // Primary: Get tenant_id from authenticated user
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && isset($user->tenant_id)) {
                return $user->tenant_id;
            }
        }

        // Fallback: Get tenant_id from session (for backward compatibility)
        $sessionTenantId = session('tenant_id');
        if ($sessionTenantId) {
            return (int) $sessionTenantId;
        }

        return null;
    }
}
