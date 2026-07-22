<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Paginator::useTailwind();

        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasRole('super-admin') ? true : null;
        });

        RateLimiter::for('public-warranty', fn (Request $request) => [
            Limit::perMinute(120)->by($request->ip()),
        ]);

        RateLimiter::for('public-warranty-search', fn (Request $request) => [
            Limit::perMinute(30)->by($request->ip()),
        ]);

        if (app()->isProduction()) {
            URL::forceRootUrl(rtrim((string) config('app.url'), '/'));

            if (filter_var(config('app.force_https', false), FILTER_VALIDATE_BOOL)) {
                URL::forceScheme('https');
            }
        }
    }
}
