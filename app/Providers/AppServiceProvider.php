<?php

namespace App\Providers;

use App\Services\StaffAvailabilityService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register StaffAvailabilityService as singleton
        $this->app->singleton(StaffAvailabilityService::class, function ($app) {
            return new StaffAvailabilityService();
        });
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
