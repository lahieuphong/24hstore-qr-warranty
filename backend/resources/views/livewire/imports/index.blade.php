<div class="space-y-6">
    <x-flash />
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-rose-700">Nạp dữ liệu hàng loạt</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Import sản phẩm từ Excel</h1>
            <p class="mt-2 text-sm text-slate-500">Hỗ trợ XLSX, XLS và CSV; các dòng hợp lệ vẫn được nhập khi một số dòng khác có lỗi.</p>
        </div>
        <a href="{{ route('admin.imports.template') }}" class="btn-secondary">Tải file mẫu XLSX</a>
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
</div>
