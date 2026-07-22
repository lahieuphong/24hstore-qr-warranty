<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#9f1239">
    <title>{{ $title ?? 'Tra cứu bảo hành' }} | 24hStore</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('admin-favicon.svg') }}?v=2">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-[#fff8fa]">
    <div class="public-shell min-h-screen">
        <header class="public-site-header relative z-30">
            <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-5 sm:px-6 lg:px-8">
                <a href="{{ route('warranty.check') }}" class="group flex items-center gap-3" aria-label="Về trang tra cứu bảo hành">
                    <img
                        src="{{ asset('admin-favicon.svg') }}?v=2"
                        alt=""
                        class="size-10 shrink-0 rounded-lg shadow-sm transition group-hover:scale-[1.03]"
                        width="40"
                        height="40"
                        aria-hidden="true"
                    >
                    <span>
                        <span class="block text-[15px] font-bold leading-5 text-slate-950">24hStore</span>
                        <span class="block text-xs leading-4 text-slate-500">Trung tâm bảo hành</span>
                    </span>
                </a>

                <div class="flex items-center gap-3">
                    <a href="{{ route('warranty.check') }}" class="hidden text-sm font-semibold text-slate-600 transition hover:text-rose-800 sm:inline-flex">
                        Tra cứu IMEI
                    </a>
                    <span class="public-live-badge hidden items-center gap-2 rounded-full px-3.5 py-2 text-xs font-semibold sm:inline-flex">
                        <span class="relative flex size-2" aria-hidden="true">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-600"></span>
                        </span>
                        Dữ liệu trực tiếp
                    </span>
                </div>
            </div>
        </header>

        <main id="main-content" class="flex-1">
            {{ $slot }}
        </main>

        <footer class="public-site-footer relative z-20">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>Thông tin bảo hành được quản lý tập trung và cập nhật trực tiếp từ hệ thống.</p>
                <p class="font-semibold text-slate-600">24hStore Care</p>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
