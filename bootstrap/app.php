<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpFoundation\Response;

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

            return AuthenticatedSessionController::loginUrl($next);
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
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            if ($response->getStatusCode() !== 419 || ! $request->routeIs('login.store')) {
                return $response;
            }

            return redirect()
                ->to(AuthenticatedSessionController::loginUrl(
                    $request->input('next'),
                    ['expired' => 1],
                ), 303)
                ->withInput($request->only('email', 'remember'));
        });
    })
    ->create();
