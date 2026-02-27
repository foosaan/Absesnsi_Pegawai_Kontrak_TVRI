<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;

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
        Paginator::useTailwind();

        // Share unread notifications with navbar
        View::composer('partials._navbar-admin-one', function ($view) {
            $notifications = collect();

            if (Auth::check()) {
                $notifications = \App\Models\Notification::where('user_id', Auth::id())
                    ->unread()
                    ->latest()
                    ->take(10)
                    ->get();
            }

            $view->with('notifications', $notifications);
        });
    }
}