<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-admin-restaurants', function ($user = null) {
            return session('role') === 'SuperAdmin';
        });

         Gate::define('view-owner', function ($user = null) {
            return session('role') === 'Owner';
        });

         Gate::define('view-staff', function ($user = null) {
            return session('role') === 'Staff';
        });

        Gate::define('view-owner-or-admin-restaurants', function($user = null) {
        return session('role') === 'Owner' ||session('role') === 'SuperAdmin';
    });
    }
}
