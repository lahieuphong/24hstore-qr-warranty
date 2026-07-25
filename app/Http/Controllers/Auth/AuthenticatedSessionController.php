<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function legacy(): RedirectResponse
    {
        return redirect()->to(self::loginUrl());
    }

    public function create(Request $request): Response
    {
        return response()
            ->view('auth.login', [
                'next' => self::safeNext($request->query('next')),
            ])
            ->withHeaders(self::noStoreHeaders());
    }

    public function csrf(Request $request): JsonResponse
    {
        return response()
            ->json([
                'token' => $request->session()->token(),
            ])
            ->withHeaders(self::noStoreHeaders());
    }

    public function store(Request $request, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = Str::lower(trim((string) $credentials['email']));
        $key = Str::transliterate($credentials['email'].'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Thử đăng nhập quá nhiều lần. Vui lòng đợi {$seconds} giây.",
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ]);
        }

        if (! $request->user()?->is_active) {
            Auth::logout();
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'email' => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $activityLogger->record(
            'auth.login',
            'Đăng nhập vào trang quản trị.',
            userId: $request->user()?->id,
        );

        $request->session()->forget('url.intended');
        $request->session()->put(
            'auth.login.redirect_to',
            self::safeNext($request->input('next')),
        );

        return to_route('login.success', status: 303);
    }

    public function success(): Response
    {
        return response()
            ->view('auth.login-success', [
                'continueUrl' => route('login.success.complete'),
                'redirectDelay' => 2000,
            ])
            ->withHeaders(self::noStoreHeaders());
    }

    public function complete(Request $request): RedirectResponse
    {
        $next = self::safeNext(
            $request->session()->pull('auth.login.redirect_to'),
        );

        return redirect()->away(rtrim(url('/'), '/').$next);
    }

    public function logoutSuccess(): Response
    {
        return response()
            ->view('auth.logout-success', [
                'continueUrl' => self::loginUrl(),
                'redirectDelay' => 2000,
            ])
            ->withHeaders(self::noStoreHeaders());
    }

    public function destroy(Request $request, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $user = $request->user();
        $activityLogger->record(
            'auth.logout',
            'Đăng xuất khỏi trang quản trị.',
            userId: $user?->id,
        );

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('logout.success', status: 303);
    }

    public static function loginUrl(mixed $next = '/admin/', array $query = []): string
    {
        $parameters = [
            'next' => self::safeNext($next),
            ...$query,
        ];
        $queryString = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);

        return url('/admin/login').'/?'.str_replace('%2F', '/', $queryString);
    }

    public static function safeNext(mixed $value): string
    {
        if (! is_string($value)) {
            return '/admin/';
        }

        $next = trim($value);

        if (mb_strlen($next) > 2048) {
            return '/admin/';
        }

        if ($next === '/admin') {
            return '/admin/';
        }

        $parts = parse_url($next);
        $path = is_array($parts) ? ($parts['path'] ?? '') : '';

        if (is_string($path)) {
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $decodedPath = rawurldecode($path);

                if ($decodedPath === $path) {
                    break;
                }

                $path = $decodedPath;
            }
        }

        if (
            $next === ''
            || ! is_array($parts)
            || isset($parts['scheme'])
            || isset($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['port'])
            || ! is_string($path)
            || ! str_starts_with($path, '/')
            || str_starts_with($path, '//')
            || str_contains($path, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $path) === 1
            || in_array('..', explode('/', $path), true)
            || in_array('.', explode('/', $path), true)
            || preg_match('#^/admin(?:/|$)#', $path) !== 1
        ) {
            return '/admin/';
        }

        return $next;
    }

    /** @return array<string, string> */
    private static function noStoreHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ];
    }
}
