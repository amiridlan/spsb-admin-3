<?php

namespace Modules\Events\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Event module service bindings
        $this->app->bind(
            \Modules\Events\Contracts\EventServiceInterface::class,
            \Modules\Events\Services\EventService::class
        );

        $this->app->bind(
            \Modules\Events\Contracts\EventSpaceServiceInterface::class,
            \Modules\Events\Services\EventSpaceService::class
        );

        $this->app->bind(
            \Modules\Events\Contracts\EventStaffAssignmentServiceInterface::class,
            \Modules\Events\Services\EventStaffAssignmentService::class
        );

        $this->app->bind(
            \Modules\Events\Contracts\EventAnalyticsServiceInterface::class,
            \Modules\Events\Services\EventAnalyticsService::class
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

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Events\Console\Commands\CompletePassedEvents::class,
            ]);
        }

        // Load migrations if needed
        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views if needed
        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'events');
    }
}
