<?php

namespace App\Providers;

use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Media;
use App\Models\Person;
use App\Observers\CacheInvalidationObserver;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SiteSettingsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Person::observe(CacheInvalidationObserver::class);
        Family::observe(CacheInvalidationObserver::class);
        FamilyChild::observe(CacheInvalidationObserver::class);
        Media::observe(CacheInvalidationObserver::class);

        // Share site settings with all views
        if (Schema::hasTable('site_settings')) {
            $siteSettings = app(SiteSettingsService::class);
            View::share('siteSettings', $siteSettings);
            View::share('siteColors', $siteSettings->colors());
            View::share('siteFont', $siteSettings->font());
            View::share('siteFontUrl', $siteSettings->fontUrl());
        }
    }
}
