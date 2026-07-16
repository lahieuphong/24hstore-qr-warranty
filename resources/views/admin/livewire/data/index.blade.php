{{-- Read-only admin data browser. --}}
@php
    $listColumns = array_values(array_filter(
        $columns,
        static fn (array $column): bool => ($column['list'] ?? true) === true,
    ));
@endphp

<div class="space-y-6">
    <x-flash />

    <header class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-semibold text-indigo-600">Quản trị hệ thống</p>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    Chỉ xem
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Dữ liệu hệ thống</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Xem các bảng nghiệp vụ, tìm kiếm bản ghi và kiểm tra chi tiết. Trang này không cho phép thêm, sửa, xóa hoặc chạy câu lệnh SQL.
            </p>
        </div>

        @if ($currentResource)
            <div class="card flex items-center gap-3 px-4 py-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-indigo-50 text-lg text-indigo-700" aria-hidden="true">
                    {{ $currentResource['icon'] ?? '▦' }}
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bảng đang xem</p>
                    <p class="mt-0.5 font-bold text-slate-900">{{ $currentResource['label'] ?? ($currentResource['key'] ?? 'Dữ liệu') }}</p>
                </div>
            </div>
        @endif
    </header>

    @error('resource')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800" role="alert">
            {{ $message }}
        </div>
    @enderror

    @if (empty($resources))
        <section class="card px-6 py-16 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-2xl text-slate-500" aria-hidden="true">▦</span>
            <h2 class="mt-4 text-lg font-black text-slate-800">Chưa có bảng dữ liệu</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                Hiện không có bảng nghiệp vụ nào được cho phép hiển thị tại trang quản trị này.
            </p>
        </section>
    @else
        <div class="grid gap-6 xl:grid-cols-[18rem_minmax(0,1fr)] xl:items-start">
            <aside class="card overflow-hidden xl:sticky xl:top-24" aria-label="Danh sách bảng dữ liệu">
                <div class="border-b border-slate-200 px-4 py-4">
                    <h2 class="font-black text-slate-900">Các bảng nghiệp vụ</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Chọn một bảng để xem các bản ghi.</p>
                </div>

                <div class="grid gap-2 p-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-1">
                    @foreach ($resources as $item)
                        @php
                            $itemKey = (string) ($item['key'] ?? '');
                            $isCurrentResource = $resource === $itemKey;
                        @endphp

                        <button
                            type="button"
                            wire:key="data-resource-{{ $itemKey }}"
                            wire:click="selectResource(@js($itemKey))"
                            wire:loading.attr="disabled"
                            wire:target="selectResource"
                            @class([
                                'group flex w-full items-start gap-3 rounded-xl border px-3 py-3 text-left transition focus:outline-none focus:ring-4 focus:ring-indigo-100 disabled:cursor-wait disabled:opacity-60',
                                'border-indigo-200 bg-indigo-50' => $isCurrentResource,
                                'border-transparent hover:border-slate-200 hover:bg-slate-50' => ! $isCurrentResource,
                            ])
                            @if ($isCurrentResource) aria-current="page" @endif
                        >
                            <span @class([
                                'grid size-10 shrink-0 place-items-center rounded-xl text-lg transition',
                                'bg-indigo-600 text-white' => $isCurrentResource,
                                'bg-slate-100 text-slate-600 group-hover:bg-white' => ! $isCurrentResource,
                            ]) aria-hidden="true">
                                {{ $item['icon'] ?? '▦' }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span @class([
                                        'truncate text-sm font-bold',
                                        'text-indigo-950' => $isCurrentResource,
                                        'text-slate-800' => ! $isCurrentResource,
                                    ])>
                                        {{ $item['label'] ?? $itemKey }}
                                    </span>
                                    <span @class([
                                        'shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold tabular-nums',
                                        'bg-white text-indigo-700' => $isCurrentResource,
                                        'bg-slate-100 text-slate-600' => ! $isCurrentResource,
                                    ])>
                                        {{ number_format((int) ($item['count'] ?? 0)) }}
                                    </span>
                                </span>
                                <span class="mt-1 block line-clamp-2 text-xs leading-5 text-slate-500">
                                    {{ $item['description'] ?? 'Dữ liệu nghiệp vụ trong hệ thống.' }}
                                </span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </aside>

            <div class="min-w-0 space-y-4">
                @if (! $currentResource)
                    <section class="card px-6 py-16 text-center">
                        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-indigo-50 text-2xl text-indigo-700" aria-hidden="true">☷</span>
                        <h2 class="mt-4 text-lg font-black text-slate-800">Chọn bảng cần xem</h2>
                        <p class="mt-2 text-sm text-slate-500">Chọn một bảng trong danh sách để bắt đầu duyệt dữ liệu.</p>
                    </section>
                @else
                    <section class="card p-4 sm:p-5" aria-label="Bộ lọc dữ liệu">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                            <div class="min-w-0 flex-1">
                                <label for="data-admin-search" class="form-label">Tìm trong bảng</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-slate-400" aria-hidden="true">⌕</span>
                                    <input
                                        id="data-admin-search"
                                        type="search"
                                        wire:model.live.debounce.350ms="search"
                                        class="form-input pl-10"
                                        placeholder="Nhập từ khóa tìm kiếm..."
                                        autocomplete="off"
                                    >
                                </div>
                                @error('search')
                                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="w-full lg:w-48">
                                <label for="data-admin-per-page" class="form-label">Số dòng mỗi trang</label>
                                <select id="data-admin-per-page" wire:model.live="perPage" class="form-input">
                                    <option value="10">10 dòng</option>
                                    <option value="25">25 dòng</option>
                                    <option value="50">50 dòng</option>
                                    <option value="100">100 dòng</option>
                                </select>
                                @error('perPage')
                                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="card overflow-hidden" aria-labelledby="data-table-title">
                        <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                            <div>
                                <h2 id="data-table-title" class="font-black text-slate-900">
                                    {{ $currentResource['label'] ?? ($currentResource['key'] ?? 'Dữ liệu') }}
                                </h2>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $currentResource['description'] ?? 'Danh sách các bản ghi trong bảng.' }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm text-slate-500">
                                Kết quả:
                                <span class="font-bold tabular-nums text-slate-800">{{ number_format($rows->total()) }}</span>
                                bản ghi
                            </p>
                        </div>

                        @if (empty($listColumns))
                            <div class="px-6 py-14 text-center">
                                <p class="font-bold text-slate-700">Không thể hiển thị cấu trúc bảng.</p>
                                <p class="mt-1 text-sm text-slate-500">Bảng này chưa có cột nào được cho phép xem.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        <tr>
                                            @foreach ($listColumns as $column)
                                                @php
                                                    $columnKey = (string) ($column['key'] ?? '');
                                                    $isSorted = $sortField === $columnKey;
                                                @endphp
                                                <th scope="col" class="min-w-40 px-4 py-3 first:pl-5">
                                                    <button
                                                        type="button"
                                                        wire:click="sortBy(@js($columnKey))"
                                                        wire:loading.attr="disabled"
                                                        wire:target="sortBy"
                                                        class="inline-flex items-center gap-1.5 text-left transition hover:text-slate-900 focus:outline-none focus:text-indigo-700 disabled:cursor-wait"
                                                        title="Sắp xếp theo {{ $column['label'] ?? $columnKey }}"
                                                    >
                                                        <span>{{ $column['label'] ?? $columnKey }}</span>
                                                        @if ($isSorted)
                                                            <span class="text-indigo-600" aria-label="{{ $sortDirection === 'asc' ? 'Tăng dần' : 'Giảm dần' }}">
                                                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                                            </span>
                                                        @else
                                                            <span class="text-slate-300" aria-hidden="true">↕</span>
                                                        @endif
                                                    </button>
                                                    @if ($column['sensitive'] ?? false)
                                                        <span class="ml-1 inline-flex rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold normal-case tracking-normal text-amber-800">Đã ẩn</span>
                                                    @endif
                                                </th>
                                            @endforeach
                                            <th scope="col" class="sticky right-0 min-w-28 bg-slate-50 px-4 py-3 text-right shadow-[-8px_0_12px_-12px_rgba(15,23,42,0.35)]">
                                                Thao tác
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @forelse ($rows as $row)
                                            @php
                                                $rowId = $row['id'] ?? null;
                                            @endphp
                                            <tr wire:key="data-row-{{ $resource }}-{{ $rowId ?? $loop->index }}" class="group align-top hover:bg-slate-50/70">
                                                @foreach ($listColumns as $column)
                                                    @php
                                                        $columnKey = (string) ($column['key'] ?? '');
                                                        $displayValue = (string) ($row['values'][$columnKey] ?? '—');
                                                        $isEmptyValue = $displayValue === '—';
                                                        $isSensitive = (bool) ($column['sensitive'] ?? false);
                                                        $columnType = (string) ($column['type'] ?? 'text');
                                                    @endphp
                                                    <td class="max-w-80 px-4 py-4 first:pl-5">
                                                        @if ($columnType === 'boolean' && ! $isEmptyValue && ! $isSensitive)
                                                            <span @class([
                                                                'inline-flex rounded-full px-2.5 py-1 text-xs font-bold',
                                                                'bg-emerald-50 text-emerald-700' => $displayValue === 'Đang hoạt động',
                                                                'bg-slate-100 text-slate-600' => $displayValue !== 'Đang hoạt động',
                                                            ])>
                                                                {{ $displayValue }}
                                                            </span>
                                                        @else
                                                            <span
                                                                @class([
                                                                    'block break-words leading-6',
                                                                    'font-mono text-xs' => in_array($columnType, ['integer', 'number', 'decimal', 'json', 'uuid'], true) || $isSensitive,
                                                                    'italic text-slate-400' => $isEmptyValue,
                                                                    'font-semibold text-slate-800' => $columnKey === 'id' && ! $isEmptyValue,
                                                                    'text-slate-700' => ! $isEmptyValue && $columnKey !== 'id',
                                                                ])
                                                                title="{{ $displayValue }}"
                                                            >
                                                                {{ \Illuminate\Support\Str::limit($displayValue, 120) }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="sticky right-0 bg-white px-4 py-3 text-right shadow-[-8px_0_12px_-12px_rgba(15,23,42,0.35)] group-hover:bg-slate-50">
                                                    @if ($rowId !== null)
                                                        <button
                                                            type="button"
                                                            wire:click="viewRecord(@js($rowId))"
                                                            wire:loading.attr="disabled"
                                                            wire:target="viewRecord"
                                                            class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 transition hover:bg-indigo-100 focus:outline-none focus:ring-4 focus:ring-indigo-100 disabled:cursor-wait disabled:opacity-60"
                                                        >
                                                            Chi tiết
                                                        </button>
                                                    @else
                                                        <span class="text-xs italic text-slate-400">Không có ID</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ count($listColumns) + 1 }}" class="px-6 py-16 text-center">
                                                    <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-slate-100 text-xl text-slate-500" aria-hidden="true">⌕</span>
                                                    <p class="mt-4 font-bold text-slate-700">Không tìm thấy bản ghi.</p>
                                                    <p class="mt-1 text-sm text-slate-500">
                                                        {{ $search !== '' ? 'Thử thay đổi hoặc xóa từ khóa tìm kiếm.' : 'Bảng này hiện chưa có dữ liệu.' }}
                                                    </p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($rows->hasPages())
                                <div class="border-t border-slate-200 px-4 py-4 sm:px-5">
                                    {{ $rows->links() }}
                                </div>
                            @endif
                        @endif
                    </section>
                @endif
            </div>
        </div>
    @endif

    @if ($record !== null)
        <div
            class="fixed inset-0 z-[70] overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="record-detail-title"
            wire:click.self="closeRecord"
            wire:keydown.escape.window="closeRecord"
        >
            <div class="mx-auto my-6 max-w-3xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-5 sm:px-6">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Chi tiết bản ghi</p>
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700">Chỉ xem</span>
                        </div>
                        <h2 id="record-detail-title" class="mt-1 truncate text-xl font-black text-slate-900">
                            {{ $currentResource['label'] ?? ($currentResource['key'] ?? 'Dữ liệu') }}
                            @if ($viewingId !== null && $viewingId !== '')
                                <span class="font-mono text-base text-slate-500">#{{ $viewingId }}</span>
                            @endif
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">Giá trị nhạy cảm được che để bảo vệ thông tin hệ thống.</p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeRecord"
                        class="grid size-10 shrink-0 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200"
                        aria-label="Đóng chi tiết bản ghi"
                    >
                        ✕
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto p-5 sm:p-6">
                    @if (empty($columns))
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">
                            Không có trường dữ liệu nào được cho phép hiển thị.
                        </div>
                    @else
                        <dl class="divide-y divide-slate-100 rounded-2xl border border-slate-200">
                            @foreach ($columns as $column)
                                @php
                                    $columnKey = (string) ($column['key'] ?? '');
                                    $displayValue = (string) ($record['values'][$columnKey] ?? '—');
                                    $isEmptyValue = $displayValue === '—';
                                    $isSensitive = (bool) ($column['sensitive'] ?? false);
                                @endphp
                                <div class="grid gap-2 px-4 py-4 sm:grid-cols-[12rem_minmax(0,1fr)] sm:gap-5 sm:px-5">
                                    <dt class="flex items-start gap-2 text-sm font-bold text-slate-600">
                                        <span class="break-words">{{ $column['label'] ?? $columnKey }}</span>
                                        @if ($isSensitive)
                                            <span class="shrink-0 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800">Đã ẩn</span>
                                        @endif
                                    </dt>
                                    <dd @class([
                                        'min-w-0 whitespace-pre-wrap break-words text-sm leading-6',
                                        'font-mono text-xs' => in_array((string) ($column['type'] ?? 'text'), ['integer', 'number', 'decimal', 'json', 'uuid'], true) || $isSensitive,
                                        'italic text-slate-400' => $isEmptyValue,
                                        'text-slate-800' => ! $isEmptyValue,
                                    ])>
                                        {{ $displayValue }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>

                <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                    <button type="button" wire:click="closeRecord" class="btn-secondary">Đóng</button>
                </div>
            </div>
        </div>
    @endif
</div>
