<?php

namespace App\Observers;

use App\Jobs\InternalCompetition\AutoEnrollNewTenantJob;
use App\Models\Tenant;

class TenantObserver
{
    /**
     * Handle the Tenant "created" event.
     */
    public function created(Tenant $tenant): void
    {
        // Auto-enroll in competitions with AUTO_NEW mode
        if ($tenant->is_active) {
            AutoEnrollNewTenantJob::dispatch($tenant->id)->delay(now()->addMinutes(5));
        }
    }

    /**
     * Handle the Tenant "updated" event.
     */
    public function updated(Tenant $tenant): void
    {
        // If tenant was activated, check for auto-enrollment
        if ($tenant->wasChanged('is_active') && $tenant->is_active) {
            AutoEnrollNewTenantJob::dispatch($tenant->id)->delay(now()->addMinutes(5));
        }
    }
}
