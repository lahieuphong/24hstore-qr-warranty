<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function legacy(): RedirectResponse
    {
        return redirect()->to(url('/admin/login').'/?next=/admin/');
    }

    public function create(Request $request): View
    {
        return view('auth.login', [
            'next' => $this->safeNext($request->query('next')),
        ]);
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

        return redirect()->away(
            rtrim(url('/'), '/').$this->safeNext($request->input('next')),
        );
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

        return redirect()->to(url('/admin/login').'/?next=/admin/');
    }

    private function safeNext(mixed $value): string
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
}
