<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen">
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900">
        <div class="pointer-events-none absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 20%, rgba(129,140,248,.8), transparent 28%), radial-gradient(circle at 80% 0%, rgba(56,189,248,.45), transparent 30%), radial-gradient(circle at 70% 80%, rgba(168,85,247,.35), transparent 28%);"></div>
        <header class="relative z-10 mx-auto flex max-w-6xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-white">
                <span class="grid size-11 place-items-center rounded-2xl bg-white/10 ring-1 ring-white/20 backdrop-blur">
                    <x-lucide-qr-code class="size-6" aria-hidden="true" />
                </span>
                <span>
                    <span class="block text-sm font-black">24hStore</span>
                    <span class="block text-xs text-indigo-200">Tra cứu bảo hành</span>
                </span>
            </a>
            <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-semibold text-indigo-100 backdrop-blur">Dữ liệu trực tiếp từ hệ thống</span>
        </header>

        <main class="relative z-10 mx-auto max-w-6xl px-4 pb-12 pt-4 sm:px-6 lg:px-8">{{ $slot }}</main>

        <footer class="relative z-10 mx-auto max-w-6xl px-4 pb-8 text-center text-xs text-slate-400 sm:px-6 lg:px-8">Thông tin bảo hành được quản lý tập trung; trang này không lưu database riêng.</footer>
    </div>

    @livewireScripts
</body>
</html>
