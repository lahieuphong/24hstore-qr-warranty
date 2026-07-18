<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Date::use(CarbonImmutable::class);

        if (app()->isProduction()) {
            URL::forceRootUrl(rtrim((string) config('app.url'), '/'));

            if (filter_var(config('app.force_https', false), FILTER_VALIDATE_BOOL)) {
                URL::forceScheme('https');
            }
        }
    }
}
