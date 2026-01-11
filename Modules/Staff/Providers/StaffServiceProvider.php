<?php

namespace Modules\Staff\Providers;

use Illuminate\Support\ServiceProvider;

class StaffServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Staff module service bindings
        $this->app->bind(
            \Modules\Staff\Contracts\StaffServiceInterface::class,
            \Modules\Staff\Services\StaffService::class
        );

        $this->app->bind(
            \Modules\Staff\Contracts\StaffAvailabilityServiceInterface::class,
            \Modules\Staff\Services\StaffAvailabilityService::class
        );

        $this->app->bind(
            \Modules\Staff\Contracts\StaffAnalyticsServiceInterface::class,
            \Modules\Staff\Services\StaffAnalyticsService::class
        );

        $this->app->bind(
            \Modules\Staff\Contracts\LeaveServiceInterface::class,
            \Modules\Staff\Services\LeaveService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load module routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // Load migrations if needed
        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views if needed
        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'staff');
    }
}
