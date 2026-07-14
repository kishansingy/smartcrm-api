<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Enforce JSON responses for API
        \Illuminate\Support\Facades\Route::pattern('id', '[0-9]+');
    }
}
