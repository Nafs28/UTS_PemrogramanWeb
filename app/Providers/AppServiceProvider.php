<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::define('admin-only', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('manage-transactions', function (User $user) {
            return in_array($user->role, ['admin', 'staff']);
        });
    }
}