<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#9f1239">
    <title>Đăng nhập thành công | {{ config('admin.site_name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('admin-favicon.svg') }}?v=2">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-rose-50">
    <main class="grid min-h-screen place-items-center px-4 py-10">
        <section
            class="w-full max-w-md text-center"
            data-login-success
            data-continue-url="{{ $continueUrl }}"
            data-redirect-delay="{{ $redirectDelay }}"
        >
            <div class="mx-auto mb-5 grid size-16 place-items-center rounded-2xl bg-rose-600 shadow-lg shadow-rose-950/20">
                <img src="{{ asset('laravel-logo.svg') }}" alt="Laravel" class="h-9 w-auto">
            </div>

            <div class="rounded-3xl border border-rose-100 bg-white p-7 shadow-2xl shadow-rose-950/10 sm:p-9">
                <div role="status" aria-live="polite" aria-atomic="true">
                    <div class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                        <x-lucide-circle-check-big class="size-8" aria-hidden="true" />
                    </div>
                    <h1 class="mt-5 text-2xl font-black text-rose-950">Đăng nhập thành công</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Bạn có thể tiếp tục vào trang quản trị.</p>
                    <div class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-rose-700">
                        <x-lucide-loader-circle class="size-4 motion-safe:animate-spin" aria-hidden="true" />
                        Sẵn sàng mở trang quản trị
                    </div>
                </div>

                <div class="mt-6 h-1.5 overflow-hidden rounded-full bg-rose-100" aria-hidden="true">
                    <div class="login-success-progress h-full rounded-full bg-rose-700"></div>
                </div>

                <a href="{{ $continueUrl }}" class="btn-primary mt-7 w-full py-3">
                    Tiếp tục ngay
                    <x-lucide-arrow-right class="size-4" aria-hidden="true" />
                </a>
            </div>

            <p class="mt-5 text-xs text-slate-500">Bạn cũng có thể chọn “Tiếp tục ngay” để mở trang quản trị.</p>
        </section>
    </main>
</body>
</html>
