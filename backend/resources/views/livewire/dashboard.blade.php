<div class="space-y-6">
    <x-flash />

    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Administration</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Quản trị hệ thống QR bảo hành</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Trang quản trị custom theo cách tổ chức của Django Admin: chia module, truy cập nhanh danh sách/thêm mới và theo dõi hoạt động gần đây.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('products.create')
                <a href="{{ route('admin.products.index', ['action' => 'create']) }}" class="btn-primary">＋ Thêm sản phẩm</a>
            @endcan
            <a href="{{ config('services.frontend.url') }}" target="_blank" rel="noopener" class="btn-secondary">↗ Mở trang tra cứu</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <article class="admin-stat-card">
            <p class="admin-stat-label">Tổng sản phẩm</p>
            <p class="admin-stat-value">{{ number_format($stats['total']) }}</p>
        </article>
        <article class="admin-stat-card">
            <p class="admin-stat-label text-emerald-700">Còn bảo hành</p>
            <p class="admin-stat-value text-emerald-700">{{ number_format($stats['active']) }}</p>
        </article>
        <article class="admin-stat-card">
            <p class="admin-stat-label text-slate-600">Hết bảo hành</p>
            <p class="admin-stat-value text-slate-700">{{ number_format($stats['expired']) }}</p>
        </article>
        <article class="admin-stat-card">
            <p class="admin-stat-label text-amber-700">Đổi bảo hành</p>
            <p class="admin-stat-value text-amber-700">{{ number_format($stats['replaced']) }}</p>
        </article>
        <article class="admin-stat-card">
            <p class="admin-stat-label text-rose-700">Khóa bảo hành</p>
            <p class="admin-stat-value text-rose-700">{{ number_format($stats['locked']) }}</p>
        </article>
    </div>

    <div class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="space-y-5">
            @foreach ($modules as $module)
                @php
                    $visibleItems = collect($module['items'])->filter(fn ($item) => auth()->user()->can($item['permission']));
                @endphp

                @if ($visibleItems->isNotEmpty())
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <header class="border-b border-indigo-700 bg-indigo-600 px-5 py-4 text-white">
                            <h2 class="font-black">{{ $module['title'] }}</h2>
                            <p class="mt-1 text-xs text-indigo-100">{{ $module['description'] }}</p>
                        </header>

                        <div class="divide-y divide-slate-100">
                            @foreach ($visibleItems as $item)
                                <div class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                                    <div class="min-w-0">
                                        <a href="{{ $item['route'] }}" class="font-bold text-indigo-700 hover:text-indigo-900 hover:underline">{{ $item['name'] }}</a>
                                        <p class="mt-1 text-sm text-slate-500">{{ $item['description'] }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 sm:justify-end">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ number_format($item['count']) }} bản ghi</span>
                                        <a href="{{ $item['route'] }}" class="admin-link-action">Xem / sửa</a>
                                        @if ($item['action_route'] && auth()->user()->can($item['action_permission']))
                                            <a href="{{ $item['action_route'] }}" class="admin-link-action">{{ $item['action_label'] }}</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach

            <section class="card overflow-hidden">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="font-black text-slate-900">Trạng thái triển khai</h2>
                        <p class="mt-1 text-xs text-slate-500">Thông tin không chứa mật khẩu hoặc khóa bí mật.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $system['database_ok'] && $system['storage_writable'] ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        <span class="size-2 rounded-full bg-current"></span>
                        {{ $system['database_ok'] && $system['storage_writable'] ? 'Sẵn sàng' : 'Cần kiểm tra' }}
                    </span>
                </header>
                <dl class="grid gap-px bg-slate-200 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="bg-white p-4">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Database</dt>
                        <dd class="mt-2 flex items-center gap-2 text-sm font-bold text-slate-800"><span class="size-2 rounded-full {{ $system['database_ok'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $system['database_driver'] }}</dd>
                    </div>
                    <div class="bg-white p-4">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Queue / Cache</dt>
                        <dd class="mt-2 text-sm font-bold text-slate-800">{{ $system['queue_driver'] }} / {{ $system['cache_store'] }}</dd>
                    </div>
                    <div class="bg-white p-4">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Storage</dt>
                        <dd class="mt-2 text-sm font-bold {{ $system['storage_writable'] ? 'text-emerald-700' : 'text-rose-700' }}">{{ $system['storage_writable'] ? 'Có quyền ghi' : 'Thiếu quyền ghi' }}</dd>
                    </div>
                    <div class="bg-white p-4 sm:col-span-2 xl:col-span-3">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Frontend public</dt>
                        <dd class="mt-2 break-all font-mono text-xs text-slate-700">{{ $system['frontend_url'] }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <aside class="card h-fit overflow-hidden">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="font-black text-slate-900">Hoạt động gần đây</h2>
                    <p class="mt-1 text-xs text-slate-500">Tương tự “Recent actions” của Django Admin</p>
                </div>
                @can('activity.view')
                    <a href="{{ route('admin.activity.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Xem tất cả</a>
                @endcan
            </header>
            <div class="divide-y divide-slate-100">
                @forelse ($recentActivities as $activity)
                    <div class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-full bg-indigo-50 text-xs font-black text-indigo-700">{{ mb_strtoupper(mb_substr($activity->user?->name ?? 'H', 0, 1)) }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm leading-5 text-slate-700">{{ $activity->description }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $activity->user?->name ?? 'Hệ thống' }} · {{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-slate-500">Hoạt động sẽ xuất hiện sau khi quản trị viên thao tác.</div>
                @endforelse
            </div>
        </aside>
    </div>
</div>
