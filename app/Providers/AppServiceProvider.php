<?php

namespace App\Providers;

use App\Models\User;
use App\Support\EnsurePublicStorage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        Schema::defaultStringLength(191);

        EnsurePublicStorage::run();

        Route::bind('agent', function (string $value): User {
            return User::withTrashed()->whereKey($value)->firstOrFail();
        });

        Gate::before(function ($user, string $ability) {
            if (! $user instanceof User) {
                return null;
            }

            if ($user->hasRole('super-admin')) {
                return true;
            }

            return null;
        });
    }
}
