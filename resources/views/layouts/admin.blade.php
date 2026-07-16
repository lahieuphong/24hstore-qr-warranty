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
<body class="min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen lg:flex">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden" @click="sidebarOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-800 bg-slate-950 text-slate-100 transition-transform duration-200 lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center justify-between border-b border-slate-800 px-5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-indigo-500 font-black text-white">QR</span>
                    <span>
                        <span class="block text-sm font-bold">24hStore</span>
                        <span class="block text-xs text-slate-400">Quản lý bảo hành</span>
                    </span>
                </a>
                <button class="rounded-lg p-2 text-slate-400 hover:bg-slate-800 lg:hidden" @click="sidebarOpen = false" aria-label="Đóng menu">✕</button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-4 text-sm">
                @can('dashboard.view')
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-500 text-white' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <span class="w-5 text-center">⌂</span> Tổng quan
                    </a>
                @endcan
                @can('products.view')
                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-semibold {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.labels.*') ? 'bg-indigo-500 text-white' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <span class="w-5 text-center">▣</span> Sản phẩm & QR
                    </a>
                @endcan
                @can('products.import')
                    <a href="{{ route('admin.imports.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-semibold {{ request()->routeIs('admin.imports.*') ? 'bg-indigo-500 text-white' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <span class="w-5 text-center">⇧</span> Import Excel
                    </a>
                @endcan
                @can('users.manage')
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-indigo-500 text-white' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <span class="w-5 text-center">♙</span> Người dùng
                    </a>
                @endcan
            </nav>

            <div class="border-t border-slate-800 p-4">
                <a href="{{ route('admin.profile') }}" class="mb-3 flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-slate-900">
                    <span class="grid size-9 place-items-center rounded-full bg-slate-800 text-sm font-bold">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-semibold">{{ auth()->user()->name }}</span>
                        <span class="block truncate text-xs text-slate-400">{{ auth()->user()->roles->first()?->name ?? 'user' }}</span>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border border-slate-700 px-3 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-900 hover:text-white">
                        Đăng xuất
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
                <button class="rounded-xl border border-slate-200 p-2.5 text-slate-700 lg:hidden" @click="sidebarOpen = true" aria-label="Mở menu">☰</button>
                <div class="hidden sm:block">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hệ thống nội bộ</p>
                    <p class="text-sm font-bold text-slate-800">QR bảo hành theo IMEI</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-800">{{ now()->format('d/m/Y') }}</p>
                    <p class="text-xs text-slate-500">Asia/Ho_Chi_Minh</p>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
