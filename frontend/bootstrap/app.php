<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Frontend public không có đăng nhập và không truy cập database.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sử dụng trang lỗi mặc định của Laravel.
    })
    ->create();
