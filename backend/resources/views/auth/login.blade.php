<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#417690">
    <title>Đăng nhập | {{ config('admin.site_name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('admin-favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950">
    <main class="grid min-h-screen place-items-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center text-white">
                <div class="mx-auto mb-4 grid size-16 place-items-center rounded-2xl bg-indigo-500 text-xl font-black shadow-lg shadow-indigo-900/30">QR</div>
                <h1 class="text-2xl font-black">24hStore QR Warranty</h1>
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
                        <input id="password" name="password" type="password" required autocomplete="current-password" class="form-input">
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input name="remember" type="checkbox" value="1" class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
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
