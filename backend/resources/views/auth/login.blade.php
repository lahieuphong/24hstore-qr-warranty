<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#9f1239">
    <title>Đăng nhập | {{ config('admin.site_name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('admin-favicon.svg') }}?v=2">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-rose-950">
    <main class="grid min-h-screen place-items-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center text-white">
                <div class="mx-auto mb-4 grid size-16 place-items-center rounded-2xl bg-rose-600 shadow-lg shadow-rose-950/30">
                    <img src="{{ asset('laravel-logo.svg') }}" alt="Laravel" class="h-9 w-auto">
                </div>
                <h1 class="text-2xl font-black text-white">24hStore QR Warranty</h1>
                <p class="mt-2 text-sm text-slate-400">Đăng nhập khu vực quản trị nội bộ</p>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-2xl sm:p-8">
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="next" value="{{ $next }}">
                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-input" placeholder="admin@gmail.com">
                    </div>
                    <div>
                        <label for="password" class="form-label">Mật khẩu</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required autocomplete="current-password" class="form-input pr-12">
                            <button
                                type="button"
                                data-password-toggle
                                aria-controls="password"
                                aria-label="Hiện mật khẩu"
                                aria-pressed="false"
                                title="Hiện mật khẩu"
                                class="absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center rounded-r-md text-slate-400 transition hover:text-rose-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-rose-300"
                            >
                                <svg data-password-eye-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5" aria-hidden="true">
                                    <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg data-password-eye-closed viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden size-5" aria-hidden="true">
                                    <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="3" />
                                    <path d="m3.5 3.5 17 17" stroke-linecap="round" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input name="remember" type="checkbox" value="1" class="size-4 rounded border-slate-300 text-rose-700 focus:ring-rose-500">
                        Ghi nhớ đăng nhập trên thiết bị này
                    </label>
                    <button type="submit" class="btn-primary w-full py-3">Đăng nhập</button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">Không có đăng ký công khai. Tài khoản do quản trị viên cấp.</p>
        </div>
    </main>
</body>
</html>
