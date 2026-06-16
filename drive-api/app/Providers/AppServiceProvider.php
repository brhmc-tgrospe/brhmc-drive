<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        // Implicitly grant "Developer" role all permissions, and also check custom legacy permissions
        Gate::before(function ($user, $ability) {
            \Log::info('Gate::before called', ['id' => $user->id ?? null, 'role' => $user->role ?? 'NULL', 'ability' => $ability]);
            if (strtolower($user->role) === 'developer' || strtolower($user->role) === 'administrator' || strtolower($user->role) === 'admin') {
                return true;
            }
            
            if (method_exists($user, 'hasPermission') && $user->hasPermission($ability)) {
                return true;
            }
            
            return null;
        });
    }
}
