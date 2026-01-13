<?php

namespace App\Providers;

use App\Policies\LeaveRequestPolicy;
use App\Services\StaffAvailabilityService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Staff\Models\LeaveRequest;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        LeaveRequest::class => LeaveRequestPolicy::class,
    ];

    public function register(): void
    {
        // Register StaffAvailabilityService as singleton
        $this->app->singleton(StaffAvailabilityService::class, function ($app) {
            return new StaffAvailabilityService();
        });
    }

    public function boot(): void
    {
        // Register policies manually (needed for modules)
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
