<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\WorkOrder;
use App\Observers\WorkOrderObserver;
use App\Policies\AssetPolicy;
use App\Policies\WorkOrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Observers
        WorkOrder::observe(WorkOrderObserver::class);

        // Policies
        Gate::policy(WorkOrder::class, WorkOrderPolicy::class);
        Gate::policy(Asset::class, AssetPolicy::class);

        // Admins bypass all gates
        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) return true;
        });
    }
}
