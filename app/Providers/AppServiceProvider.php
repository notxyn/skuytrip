<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Attraction;
use App\Observers\AttractionObserver;

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
        Attraction::observe(AttractionObserver::class);
    }
}
