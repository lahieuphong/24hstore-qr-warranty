<div
    class="space-y-6"
    x-data
    x-on:template-preview-closed.window="$nextTick(() => $refs.templatePreviewTrigger.focus())"
>
    <x-flash />
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-rose-700">Nạp dữ liệu hàng loạt</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Import sản phẩm từ Excel</h1>
            <p class="mt-2 text-sm text-slate-500">Hỗ trợ XLSX, XLS và CSV; các dòng hợp lệ vẫn được nhập khi một số dòng khác có lỗi.</p>
        </div>
        <button
            type="button"
            wire:click="openTemplatePreview"
            x-ref="templatePreviewTrigger"
            class="btn-excel-soft"
            aria-haspopup="dialog"
            aria-controls="template-preview-dialog"
            aria-expanded="{{ $showTemplatePreview ? 'true' : 'false' }}"
        >
            <x-lucide-file-spreadsheet class="size-4" aria-hidden="true" />
            Tải file mẫu XLSX
        </button>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="card p-6 xl:col-span-2">
            <h2 class="text-lg font-black text-slate-900">Chọn file dữ liệu</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Giữ nguyên hàng tiêu đề của file mẫu. Nên định dạng cột IMEI là Text để tránh Excel hiển thị dạng số mũ hoặc làm mất số 0 đầu.</p>

            <form wire:submit="import" class="mt-6 space-y-5">
                <div>
                    <label for="excel-file" class="form-label">File Excel <span class="text-rose-600">*</span></label>
                    <input
                        id="excel-file"
                        type="file"
                        wire:model="file"
                        accept=".xlsx,.xls,.csv"
                        class="block w-full rounded-md border border-dashed border-slate-300 bg-rose-50/50 px-4 py-6 text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-rose-700 file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-rose-800"
                    >
                    @error('file') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <p wire:loading wire:target="file" class="mt-2 text-xs font-semibold text-rose-700">Đang tải file lên...</p>
                </div>

                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="import,file">
                    <span wire:loading.remove wire:target="import">Bắt đầu import</span>
                    <span wire:loading wire:target="import">Đang xử lý...</span>
                </button>
            </form>
        </section>

        <aside class="card p-6">
            <h2 class="text-lg font-black text-slate-900">Cột được hỗ trợ</h2>
            <div class="mt-4 space-y-3 text-sm">
                <div class="rounded-xl bg-slate-50 p-3"><span class="font-bold">Mã hàng</span> <span class="text-rose-600">*</span></div>
                <div class="rounded-xl bg-slate-50 p-3"><span class="font-bold">Tên hàng</span> <span class="text-rose-600">*</span></div>
                <div class="rounded-xl bg-slate-50 p-3"><span class="font-bold">IMEI</span> <span class="text-rose-600">*</span></div>
                <div class="rounded-xl bg-slate-50 p-3"><span class="font-bold">Ngày nhập</span> <span class="text-rose-600">*</span></div>
                <div class="rounded-xl bg-slate-50 p-3"><span class="font-bold">Thời hạn bảo hành</span> <span class="text-slate-500">(tháng)</span></div>
            </div>
            <p class="mt-4 text-xs leading-5 text-slate-500">Ngày chấp nhận dạng dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd hoặc ngày số nội bộ của Excel.</p>
        </aside>
    </div>

    @if ($latestBatch)
        <section class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="font-black text-slate-900">Kết quả vừa import</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $latestBatch->original_filename }}</p>
                </div>
                <div class="flex gap-2 text-xs font-bold">
                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700">Thành công: {{ $latestBatch->success_rows }}</span>
                    <span class="rounded-full bg-rose-50 px-3 py-1.5 text-rose-700">Lỗi: {{ $latestBatch->failed_rows }}</span>
                </div>
            </div>

            @if ($latestBatch->errors)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Dòng</th>
                                <th class="px-5 py-3">IMEI</th>
                                <th class="px-5 py-3">Lỗi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($latestBatch->errors as $error)
                                <tr>
                                    <td class="px-5 py-4 font-bold text-slate-700">{{ $error['row'] }}</td>
                                    <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $error['imei'] ?: '—' }}</td>
                                    <td class="px-5 py-4 text-rose-700">{{ $error['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-sm font-semibold text-emerald-700">Tất cả dòng dữ liệu đều hợp lệ.</div>
            @endif
        </section>
    @endif

    <section class="card overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-black text-slate-900">Lịch sử import</h2>
            <p class="mt-1 text-xs text-slate-500">Các lần nạp dữ liệu gần đây</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">File</th>
                        <th class="px-5 py-3">Người thực hiện</th>
                        <th class="px-5 py-3">Tổng</th>
                        <th class="px-5 py-3">Thành công</th>
                        <th class="px-5 py-3">Lỗi</th>
                        <th class="px-5 py-3">Thời gian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($batches as $batch)
                        <tr>
                            <td class="max-w-xs truncate px-5 py-4 font-semibold text-slate-900">{{ $batch->original_filename }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $batch->user?->name ?? 'Hệ thống' }}</td>
                            <td class="px-5 py-4 font-bold text-slate-700">{{ $batch->total_rows }}</td>
                            <td class="px-5 py-4 font-bold text-emerald-700">{{ $batch->success_rows }}</td>
                            <td class="px-5 py-4 font-bold text-rose-700">{{ $batch->failed_rows }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $batch->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Chưa có lịch sử import.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($batches->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $batches->links() }}</div>
        @endif
    </section>

    @if ($showTemplatePreview)
        <div
            id="template-preview-dialog"
            class="fixed inset-0 z-[80] grid place-items-center overflow-y-auto bg-slate-950/55 p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="template-preview-title"
            aria-describedby="template-preview-description"
            wire:key="template-preview-modal"
            x-data
            x-init="$nextTick(() => $refs.closeTemplatePreview.focus())"
            x-trap.inert.noscroll="true"
            x-on:keydown.escape.window="$wire.closeTemplatePreview()"
            x-on:click.self="$wire.closeTemplatePreview()"
        >
            <section class="flex max-h-[calc(100dvh-2rem)] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/25 sm:max-h-[calc(100dvh-3rem)]">
                <header class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6 sm:py-5">
                    <div class="flex min-w-0 items-start gap-3.5">
                        <div class="grid size-11 shrink-0 place-items-center rounded-2xl bg-rose-100 text-rose-700 ring-4 ring-rose-50">
                            <x-lucide-file-spreadsheet class="size-6" aria-hidden="true" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-700">Xem trước biểu mẫu</p>
                            <h2 id="template-preview-title" class="mt-1 text-xl font-black text-slate-900 sm:text-2xl">File mẫu import sản phẩm</h2>
                            <p id="template-preview-description" class="mt-1 text-sm leading-5 text-slate-500">Kiểm tra cấu trúc và dữ liệu mẫu trước khi tải file XLSX về máy.</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeTemplatePreview"
                        x-ref="closeTemplatePreview"
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-rose-100"
                        aria-label="Đóng bản xem trước file mẫu"
                    >
                        <x-lucide-x class="size-5" aria-hidden="true" />
                    </button>
                </header>

                <div class="overflow-y-auto px-5 py-5 sm:px-6 sm:py-6">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-bold text-slate-800">mau-import-san-pham.xlsx</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ count($templateHeadings) }} cột · {{ count($templateRows) }} dòng dữ liệu mẫu</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-200">
                            <x-lucide-eye class="size-3.5" aria-hidden="true" />
                            Chế độ xem trước
                        </span>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-[#a6cbb6] bg-white shadow-sm">
                        <div class="flex items-center justify-between gap-3 border-b border-[#c6ddd0] bg-[#f3f8f5] px-4 py-2.5">
                            <div class="flex items-center gap-2 text-xs font-semibold text-[#185c37]">
                                <x-lucide-table-2 class="size-4 text-[#217346]" aria-hidden="true" />
                                Bảng dữ liệu mẫu
                            </div>
                            <span class="rounded-md border border-[#b7d5c3] bg-white px-2 py-1 text-[11px] font-semibold text-[#185c37]">Chỉ xem</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[860px] table-fixed border-collapse text-left text-xs" aria-label="Bản xem trước nội dung file mẫu XLSX">
                                <colgroup>
                                    <col class="w-12">
                                    <col class="w-36">
                                    <col class="w-64">
                                    <col class="w-52">
                                    <col class="w-40">
                                    <col class="w-52">
                                </colgroup>
                                <thead>
                                    <tr class="bg-[#e7f0ea] text-center font-bold text-[#466354]" aria-hidden="true">
                                        <th class="border-b border-r border-[#b7cdbf] px-2 py-2"></th>
                                        @foreach ($templateHeadings as $index => $heading)
                                            <th class="border-b border-r border-[#b7cdbf] px-3 py-2 last:border-r-0">{{ chr(65 + $index) }}</th>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-[#d9eadf] font-bold text-[#173f2b]">
                                        <th scope="row" class="border-b border-r border-[#b7cdbf] bg-[#e7f0ea] px-2 py-3 text-center text-[#466354]">1</th>
                                        @foreach ($templateHeadings as $heading)
                                            <th scope="col" class="border-b border-r border-[#b7cdbf] px-3 py-3 last:border-r-0">{{ $heading }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($templateRows as $rowIndex => $row)
                                        <tr class="template-preview-data-row bg-white text-slate-700">
                                            <th scope="row" class="border-b border-r border-[#cbd8d0] bg-[#f3f8f5] px-2 py-3 text-center font-bold text-[#688071]">{{ $rowIndex + 2 }}</th>
                                            @foreach ($row as $cellIndex => $cell)
                                                <td class="border-b border-r border-[#cbd8d0] px-3 py-3 last:border-r-0 {{ $cellIndex === 2 ? 'font-mono font-semibold text-slate-900' : '' }}">{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center border-t border-[#c6ddd0] bg-[#f3f8f5] px-4 py-2">
                            <span class="inline-flex items-center gap-2 border-b-2 border-[#217346] px-3 py-1 text-xs font-bold text-[#185c37]">
                                <x-lucide-sheet class="size-3.5" aria-hidden="true" />
                                Sản phẩm
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-600">
                                <x-lucide-badge-info class="size-4 text-rose-700" aria-hidden="true" />
                                Cột IMEI
                            </p>
                            <p class="mt-1.5 text-xs leading-5 text-slate-500">Đã định dạng dạng Text để giữ nguyên số 0 ở đầu và đủ 15 chữ số.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-600">
                                <x-lucide-calendar-days class="size-4 text-rose-700" aria-hidden="true" />
                                Cột ngày nhập
                            </p>
                            <p class="mt-1.5 text-xs leading-5 text-slate-500">File mẫu hiển thị ngày theo định dạng dd/mm/yyyy để dễ nhập và kiểm tra.</p>
                        </div>
                    </div>
                </div>

                <footer class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <p class="text-xs leading-5 text-slate-500">Bạn vẫn có thể chỉnh sửa các dòng trong Excel sau khi tải xuống.</p>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <button type="button" wire:click="closeTemplatePreview" class="btn-secondary">Đóng</button>
                        <a
                            href="{{ route('admin.imports.template') }}"
                            download="mau-import-san-pham.xlsx"
                            class="btn-primary"
                        >
                            <x-lucide-download class="size-4" aria-hidden="true" />
                            Tải file về máy
                        </a>
                    </div>
                </footer>
            </section>
        </div>
    @endif
</div>
