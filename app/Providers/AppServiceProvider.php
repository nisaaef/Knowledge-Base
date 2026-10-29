<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
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
            if (request()->header('X-Forwarded-Proto') === 'https') {
                URL::forceScheme('https');
            }

            if (request()->getHost() !== parse_url(config('app.url'), PHP_URL_HOST)) {
                URL::forceRootUrl(request()->getSchemeAndHttpHost());
            }
        }
}
