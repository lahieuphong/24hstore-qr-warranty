<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#9f1239">
    <title>{{ $title ?? 'Site administration' }} | {{ config('admin.site_name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('admin-favicon.svg') }}?v=2">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="django-admin-shell min-h-screen" x-data="{ adminMenuOpen: false }">
    @php
        $breadcrumbSection = match (true) {
            request()->routeIs('admin.products.*'), request()->routeIs('admin.labels.*'), request()->routeIs('admin.imports.*') => 'Bảo hành & kho',
            request()->routeIs('admin.users.*') => 'Xác thực & phân quyền',
            request()->routeIs('admin.activity.*') => 'Vận hành hệ thống',
            request()->routeIs('admin.profile') => 'Tài khoản',
            default => null,
        };
    @endphp
    <header class="django-masthead">
        <div class="mx-auto flex min-h-16 max-w-[1600px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/admin').'/' }}" class="group flex min-w-0 items-center gap-3" aria-label="Về trang quản trị">
                <span class="grid size-10 shrink-0 place-items-center rounded-md border border-white/20 bg-white/10 shadow-sm">
                    <img src="{{ asset('laravel-logo.svg') }}" alt="" class="h-6 w-auto" aria-hidden="true">
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-lg font-medium tracking-tight text-rose-100 sm:text-xl">{{ config('admin.site_name') }}</span>
                    <span class="block truncate text-[11px] font-medium uppercase tracking-[0.14em] text-rose-100/75">{{ config('admin.site_tagline') }}</span>
                </span>
            </a>

            <button
                type="button"
                class="inline-flex size-10 items-center justify-center rounded-md border border-white/25 text-white transition hover:bg-white/10 lg:hidden"
                @click="adminMenuOpen = ! adminMenuOpen"
                :aria-expanded="adminMenuOpen"
                aria-controls="admin-mobile-menu"
                aria-label="Mở menu quản trị"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                </svg>
            </button>

            <div class="hidden items-center gap-2 text-xs font-medium uppercase tracking-wide text-white/90 lg:flex">
                <span class="mr-1">Xin chào, <strong class="text-white">{{ auth()->user()->name }}</strong></span>
                <span class="text-white/40">/</span>
                <a href="{{ config('services.frontend.url') }}" target="_blank" rel="noopener" class="django-utility-link">Xem trang tra cứu</a>
                <span class="text-white/40">/</span>
                <a href="{{ route('admin.profile') }}" class="django-utility-link">Đổi mật khẩu</a>
                <span class="text-white/40">/</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="django-utility-link uppercase tracking-wide">Đăng xuất</button>
                </form>
            </div>
        </div>

        <div
            id="admin-mobile-menu"
            x-cloak
            x-show="adminMenuOpen"
            x-transition.origin.top
            class="border-t border-white/15 bg-[#881337] px-4 py-3 lg:hidden"
        >
            <nav class="mx-auto grid max-w-[1600px] gap-1 text-sm text-white">
                @can('dashboard.view')
                    <a href="{{ url('/admin').'/' }}" class="django-mobile-nav-link {{ request()->routeIs('admin.dashboard') ? 'bg-white/15' : '' }}">Trang quản trị</a>
                @endcan
                @can('products.view')
                    <a href="{{ route('admin.products.index') }}" class="django-mobile-nav-link">Sản phẩm & QR</a>
                @endcan
                @can('products.import')
                    <a href="{{ route('admin.imports.index') }}" class="django-mobile-nav-link">Import Excel</a>
                @endcan
                @can('users.manage')
                    <a href="{{ route('admin.users.index') }}" class="django-mobile-nav-link">Người dùng</a>
                @endcan
                @can('activity.view')
                    <a href="{{ route('admin.activity.index') }}" class="django-mobile-nav-link">Hoạt động quản trị</a>
                @endcan
                <a href="{{ route('admin.profile') }}" class="django-mobile-nav-link">Hồ sơ / Đổi mật khẩu</a>
                <a href="{{ config('services.frontend.url') }}" target="_blank" rel="noopener" class="django-mobile-nav-link">Mở trang tra cứu ↗</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-white/15 pt-2">
                    @csrf
                    <button type="submit" class="django-mobile-nav-link w-full text-left">Đăng xuất</button>
                </form>
            </nav>
        </div>
    </header>

    <div class="django-breadcrumb-bar">
        <nav class="mx-auto flex min-h-10 max-w-[1600px] items-center gap-2 px-4 text-xs sm:px-6 lg:px-8" aria-label="Breadcrumb">
            <a href="{{ url('/admin').'/' }}" class="font-semibold text-white hover:underline">Trang chủ</a>
            @unless (request()->routeIs('admin.dashboard'))
                <svg viewBox="0 0 20 20" fill="currentColor" class="size-3.5 text-white/60" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.22 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.22 6.28a.75.75 0 0 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd" />
                </svg>
                @if ($breadcrumbSection)
                    <span class="font-medium text-white/75">{{ $breadcrumbSection }}</span>
                    <svg viewBox="0 0 20 20" fill="currentColor" class="size-3.5 text-white/60" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.22 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.22 6.28a.75.75 0 0 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd" />
                    </svg>
                @endif
                <span class="font-medium text-white/90">{{ $title ?? 'Administration' }}</span>
            @endunless
        </nav>
    </div>

    <main class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        {{ $slot }}
    </main>

    <footer class="mx-auto max-w-[1600px] px-4 pb-8 pt-2 text-center text-xs text-slate-400 sm:px-6 lg:px-8">
        {{ config('admin.site_name') }} · Laravel & Livewire administration
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
