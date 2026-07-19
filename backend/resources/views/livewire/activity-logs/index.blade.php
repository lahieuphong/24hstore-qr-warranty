<div class="space-y-6">
    <x-flash />

    <div>
        <p class="text-sm font-semibold text-rose-700">Nhật ký hệ thống</p>
        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Hoạt động quản trị</h1>
        <p class="mt-2 text-sm text-slate-500">Theo dõi đăng nhập và các thao tác thay đổi dữ liệu quan trọng.</p>
    </div>

    <section class="card overflow-hidden">
        <div class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-[1fr_240px_120px]">
            <label class="relative block">
                <span class="sr-only">Tìm hoạt động</span>
                <input wire:model.live.debounce.350ms="search" class="form-input pl-10" placeholder="Tìm mô tả, người thao tác...">
                <x-lucide-search class="pointer-events-none absolute left-3 top-3 size-4 text-slate-400" aria-hidden="true" />
            </label>
            <select wire:model.live="action" class="form-input">
                <option value="">Tất cả hành động</option>
                @foreach ($actions as $actionName)
                    <option value="{{ $actionName }}">{{ $actionName }}</option>
                @endforeach
            </select>
            <select wire:model.live="perPage" class="form-input">
                <option value="25">25 dòng</option>
                <option value="50">50 dòng</option>
                <option value="100">100 dòng</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Thời gian</th>
                        <th class="px-5 py-3">Người thao tác</th>
                        <th class="px-5 py-3">Hành động</th>
                        <th class="px-5 py-3">Chi tiết</th>
                        <th class="px-5 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($logs as $log)
                        <tr class="align-top hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                <p class="font-semibold text-slate-800">{{ $log->created_at->format('d/m/Y') }}</p>
                                <p class="mt-1 text-xs">{{ $log->created_at->format('H:i:s') }}</p>
                            </td>
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
