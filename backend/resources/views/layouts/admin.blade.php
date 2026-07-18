<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? config('admin.site_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen lg:flex">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="sidebarOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-800 bg-slate-950 text-slate-100 transition-transform duration-200 lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center justify-between border-b border-slate-800 px-5">
                <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-indigo-500 font-black text-white">QR</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold">{{ config('admin.site_name') }}</span>
                        <span class="block truncate text-xs text-slate-400">{{ config('admin.site_tagline') }}</span>
                    </span>
                </a>
                <button class="rounded-lg p-2 text-slate-400 hover:bg-slate-800 lg:hidden" @click="sidebarOpen = false" aria-label="Đóng menu">✕</button>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 text-sm">
                <p class="mb-2 px-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Trang quản trị</p>
                <div class="space-y-1">
                    @can('dashboard.view')
                        <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'admin-nav-link-active' : '' }}"><span class="w-5 text-center">⌂</span> Tổng quan</a>
                    @endcan
                </div>

                <p class="mb-2 mt-6 px-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Bảo hành & kho</p>
                <div class="space-y-1">
                    @can('products.view')
                        <a href="{{ route('admin.products.index') }}" class="admin-nav-link {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.labels.*') ? 'admin-nav-link-active' : '' }}"><span class="w-5 text-center">▣</span> Sản phẩm & QR</a>
                    @endcan
                    @can('products.import')
                        <a href="{{ route('admin.imports.index') }}" class="admin-nav-link {{ request()->routeIs('admin.imports.*') ? 'admin-nav-link-active' : '' }}"><span class="w-5 text-center">⇧</span> Import Excel</a>
                    @endcan
                </div>

                <p class="mb-2 mt-6 px-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Tài khoản & hệ thống</p>
                <div class="space-y-1">
                    @can('users.manage')
                        <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'admin-nav-link-active' : '' }}"><span class="w-5 text-center">♙</span> Người dùng</a>
                    @endcan
                    @can('activity.view')
                        <a href="{{ route('admin.activity.index') }}" class="admin-nav-link {{ request()->routeIs('admin.activity.*') ? 'admin-nav-link-active' : '' }}"><span class="w-5 text-center">◴</span> Hoạt động</a>
                    @endcan
                </div>
            </nav>

            <div class="border-t border-slate-800 p-4">
                <a href="{{ config('services.frontend.url') }}" target="_blank" rel="noopener" class="mb-3 flex items-center justify-between rounded-xl border border-slate-800 px-3 py-2.5 text-xs font-bold text-slate-300 hover:bg-slate-900 hover:text-white">
                    <span>Mở trang tra cứu</span><span>↗</span>
                </a>
                <a href="{{ route('admin.profile') }}" class="mb-3 flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-slate-900">
                    <span class="grid size-9 place-items-center rounded-full bg-slate-800 text-sm font-bold">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-semibold">{{ auth()->user()->name }}</span>
                        <span class="block truncate text-xs text-slate-400">{{ auth()->user()->roles->first()?->name ?? 'user' }}</span>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border border-slate-700 px-3 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-900 hover:text-white">Đăng xuất</button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button class="rounded-xl border border-slate-200 p-2.5 text-slate-700 lg:hidden" @click="sidebarOpen = true" aria-label="Mở menu">☰</button>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Backend Administration</p>
                        <p class="text-sm font-bold text-slate-800">Dữ liệu tập trung · Livewire UI</p>
                    </div>
                </div>
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold text-slate-800">{{ now()->format('d/m/Y') }}</p>
                    <p class="text-xs text-slate-500">{{ app()->environment() }} · {{ config('database.default') }}</p>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">{{ $slot }}</main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
