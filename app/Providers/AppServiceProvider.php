<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        // Register Blade directives for permission checking
        Blade::if('can', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        Blade::if('cannot', function ($permission) {
            return !auth()->check() || !auth()->user()->hasPermission($permission);
        });

        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        Blade::if('hasRole', function ($roleName) {
            return auth()->check() && auth()->user()->hasRole($roleName);
        });
    }
}
