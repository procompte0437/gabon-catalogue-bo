<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        // En ligne (Railway), toutes les URL générées (photos comprises) doivent être en https :
        // Android et les navigateurs refusent les photos en http sur une page https.
        $host = $this->app->runningInConsole() ? '' : request()->getHost();
        $local = in_array($host, ['', '127.0.0.1', 'localhost', '::1'], true);
        if ($this->app->environment('production') || ! $local) {
            URL::forceScheme('https');
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
