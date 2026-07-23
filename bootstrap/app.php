<?php

use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Render terminates HTTPS at its cloud load balancer and forwards the
        // original scheme and client address through standard proxy headers.
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo(function (Request $request): string {
            $next = $request->is('admin') || $request->is('admin/*')
                ? $request->getRequestUri()
                : '/admin/';

            if ($next === '/admin') {
                $next = '/admin/';
            }

            $encodedNext = str_replace('%2F', '/', rawurlencode($next));

            return url('/admin/login').'/?next='.$encodedNext;
        });
        $middleware->redirectUsersTo(fn () => url('/admin').'/');

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sử dụng cơ chế xử lý ngoại lệ mặc định của Laravel.
    })
    ->create();
