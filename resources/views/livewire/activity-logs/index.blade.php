<div class="space-y-6">
    <x-flash />

    <div>
        <p class="text-sm font-semibold text-rose-700">Nhật ký hệ thống</p>
        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Hoạt động quản trị</h1>
        <p class="mt-2 text-sm text-slate-500">Theo dõi đăng nhập và các thao tác thay đổi dữ liệu quan trọng.</p>
    </div>

    <section class="card">
        <div class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-[1fr_240px_120px]">
            <label class="relative block">
                <span class="sr-only">Tìm hoạt động</span>
                <input wire:model.live.debounce.350ms="search" class="form-input form-input-leading-icon" placeholder="Tìm mô tả, người thao tác...">
                <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
            </label>
            <x-admin-select
                id="activity-action"
                label="Hành động"
                model="action"
                :value="$action"
                :options="array_merge(
                    ['' => 'Tất cả hành động'],
                    $actions->mapWithKeys(fn ($actionName) => [$actionName => $actionName])->all(),
                )"
                :live="true"
            />
            <x-admin-select
                id="activity-per-page"
                label="Số dòng"
                model="perPage"
                :value="$perPage"
                :options="[
                    25 => '25 dòng',
                    50 => '50 dòng',
                    100 => '100 dòng',
                ]"
                :live="true"
            />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Người thao tác</th>
                        <th class="px-5 py-3">Hành động</th>
                        <th class="px-5 py-3">Chi tiết</th>
                        <th class="px-5 py-3">IP</th>
                        <th class="min-w-40 px-5 py-3">Thời gian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($logs as $log)
                        <tr class="align-top hover:bg-slate-50/70">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-900">{{ $log->user?->name ?? 'Hệ thống' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $log->user?->email ?? 'Tác vụ tự động' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700">{{ $log->actionLabel() }}</span>
                                <p class="mt-1 font-mono text-[11px] text-slate-400">{{ $log->action }}</p>
                            </td>
                            <td class="max-w-xl px-5 py-4 leading-6 text-slate-700">{{ $log->description }}</td>
                            <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-slate-500">{{ $log->ip_address ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-500">
                                {{ $log->created_at?->format('d/m/Y H:i') ?? 'Không có' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-14 text-center text-slate-500">Chưa có hoạt động phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-slate-200 px-4 py-4 sm:px-5">{{ $logs->links() }}</div>
        @endif
    </section>
</div>
