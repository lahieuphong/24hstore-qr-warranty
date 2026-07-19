<div class="space-y-6">
    <x-flash />

    <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-rose-700">Administration</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-800 sm:text-3xl">Quản trị hệ thống</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Chọn một chức năng bên dưới để xem, thêm hoặc thay đổi dữ liệu bảo hành.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('products.create')
                <a href="{{ route('admin.products.index', ['action' => 'create']) }}" class="btn-primary">
                    <x-lucide-plus class="size-4" aria-hidden="true" />
                    Thêm sản phẩm
                </a>
            @endcan
            <a href="{{ config('services.frontend.url') }}" target="_blank" rel="noopener" class="btn-secondary">
                Xem trang tra cứu
                <x-lucide-external-link class="size-4" aria-hidden="true" />
            </a>
        </div>
    </div>

    <section aria-labelledby="warranty-overview-heading">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 id="warranty-overview-heading" class="text-sm font-semibold text-slate-700">Tổng quan bảo hành</h2>
            <span class="text-xs text-slate-400">Cập nhật {{ now()->format('d/m/Y') }}</span>
        </div>
        <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
            <article class="admin-stat-card">
                <p class="admin-stat-label">Tổng sản phẩm</p>
                <p class="admin-stat-value">{{ number_format($stats['total']) }}</p>
            </article>
            <article class="admin-stat-card border-l-4 border-l-emerald-500">
                <p class="admin-stat-label">Còn bảo hành</p>
                <p class="admin-stat-value text-emerald-700">{{ number_format($stats['active']) }}</p>
            </article>
            <article class="admin-stat-card border-l-4 border-l-slate-400">
                <p class="admin-stat-label">Hết bảo hành</p>
                <p class="admin-stat-value text-slate-600">{{ number_format($stats['expired']) }}</p>
            </article>
            <article class="admin-stat-card border-l-4 border-l-amber-500">
                <p class="admin-stat-label">Đổi bảo hành</p>
                <p class="admin-stat-value text-amber-700">{{ number_format($stats['replaced']) }}</p>
            </article>
            <article class="admin-stat-card border-l-4 border-l-rose-500">
                <p class="admin-stat-label">Khóa bảo hành</p>
                <p class="admin-stat-value text-rose-700">{{ number_format($stats['locked']) }}</p>
            </article>
        </div>
    </section>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-5">
            @foreach ($modules as $module)
                @php
                    $visibleItems = collect($module['items'])->filter(fn ($item) => auth()->user()->can($item['permission']));
                @endphp

                @if ($visibleItems->isNotEmpty())
                    <section class="django-module" aria-labelledby="module-{{ $loop->index }}">
                        <header class="django-module-header">
                            <div>
                                <h2 id="module-{{ $loop->index }}" class="text-sm font-bold uppercase tracking-wide text-white">{{ $module['title'] }}</h2>
                                <p class="mt-1 text-xs text-white/80">{{ $module['description'] }}</p>
                            </div>
                        </header>

                        <div class="divide-y divide-slate-200">
                            @foreach ($visibleItems as $item)
                                <div class="django-model-row">
                                    <div class="min-w-0">
                                        <a href="{{ $item['route'] }}" class="django-model-link">{{ $item['name'] }}</a>
                                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $item['description'] }}</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 sm:justify-end">
                                        <span class="shadcn-badge">{{ number_format($item['count']) }} bản ghi</span>
                                        @if ($item['action_route'] && auth()->user()->can($item['action_permission']))
                                            <a href="{{ $item['action_route'] }}" class="django-row-action django-row-action-add">
                                                <x-lucide-plus class="size-4" aria-hidden="true" />
                                                {{ $item['action_label'] }}
                                            </a>
                                        @endif
                                        <a href="{{ $item['route'] }}" class="django-row-action">
                                            <x-lucide-pencil class="size-4" aria-hidden="true" />
                                            Xem / sửa
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach

            <section class="django-module" aria-labelledby="deployment-status-heading">
                <header class="django-module-header flex items-center justify-between gap-4">
                    <div>
                        <h2 id="deployment-status-heading" class="text-sm font-bold uppercase tracking-wide text-white">Trạng thái hệ thống</h2>
                        <p class="mt-1 text-xs text-white/80">Thông tin vận hành không chứa khóa bí mật.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">
                        <span class="size-2 rounded-full {{ $system['database_ok'] && $system['storage_writable'] ? 'bg-emerald-300' : 'bg-rose-300' }}"></span>
                        {{ $system['database_ok'] && $system['storage_writable'] ? 'Sẵn sàng' : 'Cần kiểm tra' }}
                    </span>
                </header>
                <dl class="grid gap-px bg-slate-200 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="bg-white p-4">
                        <dt class="django-detail-label">Database</dt>
                        <dd class="mt-2 flex items-center gap-2 text-sm font-semibold text-slate-700"><span class="size-2 rounded-full {{ $system['database_ok'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $system['database_driver'] }}</dd>
                    </div>
                    <div class="bg-white p-4">
                        <dt class="django-detail-label">Queue / Cache</dt>
                        <dd class="mt-2 text-sm font-semibold text-slate-700">{{ $system['queue_driver'] }} / {{ $system['cache_store'] }}</dd>
                    </div>
                    <div class="bg-white p-4">
                        <dt class="django-detail-label">Storage</dt>
                        <dd class="mt-2 text-sm font-semibold {{ $system['storage_writable'] ? 'text-emerald-700' : 'text-rose-700' }}">{{ $system['storage_writable'] ? 'Có quyền ghi' : 'Thiếu quyền ghi' }}</dd>
                    </div>
                    <div class="bg-white p-4">
                        <dt class="django-detail-label">Environment</dt>
                        <dd class="mt-2 text-sm font-semibold text-slate-700">{{ $system['environment'] }}</dd>
                    </div>
                    <div class="bg-white p-4 sm:col-span-2 xl:col-span-4">
                        <dt class="django-detail-label">Frontend public</dt>
                        <dd class="mt-2 break-all font-mono text-xs text-slate-600">{{ $system['frontend_url'] }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <aside class="django-recent-actions overflow-hidden" aria-labelledby="recent-actions-heading">
            <header class="border-b border-slate-200 bg-slate-100 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="recent-actions-heading" class="text-lg font-medium text-slate-800">Hoạt động gần đây</h2>
                    @can('activity.view')
                        <a href="{{ route('admin.activity.index') }}" class="text-xs font-semibold text-rose-700 hover:text-rose-900 hover:underline">Xem tất cả</a>
                    @endcan
                </div>
                <p class="mt-2 text-xs font-bold uppercase tracking-wide text-slate-500">Hoạt động quản trị</p>
            </header>
            <div class="divide-y divide-slate-100">
                @forelse ($recentActivities as $activity)
                    <div class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 grid size-7 shrink-0 place-items-center rounded-md bg-rose-100 text-[11px] font-bold text-rose-700">{{ mb_strtoupper(mb_substr($activity->user?->name ?? 'H', 0, 1)) }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm leading-5 text-slate-700">{{ $activity->description }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $activity->user?->name ?? 'Hệ thống' }} · {{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-sm text-slate-500">Không có hoạt động gần đây.</div>
                @endforelse
            </div>
        </aside>
    </div>
</div>
