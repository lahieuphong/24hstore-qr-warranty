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
        <section class="card flex h-full flex-col p-6 xl:col-span-2">
            <h2 class="text-lg font-black text-slate-900">Chọn file dữ liệu</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Giữ nguyên hàng tiêu đề của file mẫu. Nên định dạng cột IMEI là Text để tránh Excel hiển thị dạng số mũ hoặc làm mất số 0 đầu.</p>

            <form
                wire:submit="import"
                class="mt-6 flex flex-1 flex-col gap-5"
                data-file-dropzone
                x-data="{
                        dragDepth: 0,
                        isDragging: false,
                        uploading: false,
                        resetting: false,
                        uploadProgress: 0,
                        fileName: '',
                        fileSize: '',
                        statusMessage: 'Chưa chọn tệp',
                        uploadFeedback: '',
                        formatFileSize(bytes) {
                            if (! Number.isFinite(bytes) || bytes <= 0) {
                                return '';
                            }

                            if (bytes < 1024 * 1024) {
                                return `${Math.max(1, Math.round(bytes / 1024))} KB`;
                            }

                            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
                        },
                        rememberFile(file) {
                            if (! file) {
                                return;
                            }

                            this.fileName = file.name;
                            this.fileSize = this.formatFileSize(file.size);
                            this.statusMessage = this.uploading
                                ? `Đang tải ${file.name}`
                                : `Đã chọn ${file.name}`;
                            this.uploadFeedback = '';
                        },
                        resetFileState(message = 'Chưa chọn tệp', feedback = '') {
                            this.fileName = '';
                            this.fileSize = '';
                            this.uploading = false;
                            this.uploadProgress = 0;
                            this.statusMessage = message;
                            this.uploadFeedback = feedback;

                            if (this.$refs.fileInput) {
                                this.$refs.fileInput.value = '';
                            }
                        },
                        startUpload() {
                            this.uploading = true;
                            this.resetting = false;
                            this.uploadProgress = 0;
                            this.isDragging = false;
                            this.statusMessage = `Đang tải ${this.fileName}`;
                            this.uploadFeedback = '';
                        },
                        finishUpload() {
                            this.uploading = false;
                            this.uploadProgress = 100;
                            this.statusMessage = `Đã tải ${this.fileName}`;
                        },
                        failUpload(message) {
                            this.resetting = true;
                            this.resetFileState(message, message);
                        },
                        beginDrag() {
                            if (this.uploading || this.resetting) {
                                return;
                            }

                            this.dragDepth += 1;
                            this.isDragging = true;
                        },
                        endDrag() {
                            this.dragDepth = Math.max(0, this.dragDepth - 1);

                            if (this.dragDepth === 0) {
                                this.isDragging = false;
                            }
                        },
                        receiveDrop(files) {
                            if (this.uploading || this.resetting) {
                                return;
                            }

                            this.dragDepth = 0;
                            this.isDragging = false;

                            const file = files?.[0];

                            if (! file) {
                                return;
                            }

                            this.rememberFile(file);

                            try {
                                const transfer = new DataTransfer();
                                transfer.items.add(file);
                                this.$refs.fileInput.files = transfer.files;
                            } catch (error) {
                                try {
                                    this.$refs.fileInput.files = files;
                                } catch (assignmentError) {
                                    this.resetFileState();
                                    this.$refs.fileInput.click();

                                    return;
                                }
                            }

                            this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                        },
                    }"
                    x-init="$wire.$watch('file', (value) => {
                        if (! value && ! uploading && ! uploadFeedback) {
                            resetFileState();
                        }
                    })"
                >
                <div class="flex flex-1 flex-col">
                    <label for="excel-file" class="form-label">File Excel <span class="text-rose-600">*</span></label>
                    <input
                        id="excel-file"
                        type="file"
                        wire:model="file"
                        x-ref="fileInput"
                        x-bind:disabled="uploading || resetting"
                        x-on:change="rememberFile($event.target.files?.[0])"
                        x-on:livewire-upload-start="startUpload()"
                        x-on:livewire-upload-progress="uploadProgress = $event.detail.progress"
                        x-on:livewire-upload-finish="finishUpload()"
                        x-on:livewire-upload-error="failUpload('Không thể tải tệp lên. Vui lòng chọn lại.'); $wire.clearFailedUpload().then(() => resetting = false)"
                        x-on:livewire-upload-cancel="failUpload('Đã hủy tải tệp. Vui lòng chọn lại.'); $wire.clearFailedUpload().then(() => resetting = false)"
                        accept=".xlsx,.xls,.csv"
                        class="peer sr-only"
                        data-file-input
                        aria-describedby="excel-file-help excel-file-status{{ $errors->has('file') ? ' excel-file-error' : '' }}"
                        @if ($errors->has('file')) aria-invalid="true" @endif
                    >

                    <label
                        for="excel-file"
                        class="group relative flex min-h-64 flex-1 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed px-5 py-8 text-center transition duration-200 peer-focus-visible:outline-none peer-focus-visible:ring-4 peer-focus-visible:ring-rose-100 sm:min-h-72 sm:px-8 {{ $errors->has('file') ? 'border-rose-400 bg-rose-50/70' : 'border-slate-300 bg-gradient-to-br from-white via-rose-50/45 to-slate-50 hover:border-rose-400 hover:bg-rose-50/70' }}"
                        x-bind:class="{
                            'scale-[1.01] border-rose-500 bg-rose-50 shadow-lg shadow-rose-950/10': isDragging,
                            'cursor-wait opacity-75': uploading || resetting,
                        }"
                        x-bind:aria-disabled="uploading || resetting ? 'true' : 'false'"
                        x-on:click="if (uploading || resetting) $event.preventDefault()"
                        x-on:dragenter.prevent.stop="beginDrag()"
                        x-on:dragover.prevent.stop="if (! uploading && ! resetting) isDragging = true"
                        x-on:dragleave.prevent.stop="endDrag()"
                        x-on:drop.prevent.stop="receiveDrop($event.dataTransfer.files)"
                        data-file-drop-target
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(190,18,60,0.09),_transparent_50%)] opacity-0 transition duration-200"
                            x-bind:class="{ 'opacity-100': isDragging }"
                            aria-hidden="true"
                        ></div>

                        <div class="relative mx-auto w-full max-w-xl">
                            <div x-show="! fileName" class="flex flex-col items-center">
                                <span
                                    class="grid size-16 place-items-center rounded-2xl bg-white text-rose-700 shadow-sm ring-1 ring-slate-200 transition duration-200 group-hover:-translate-y-0.5 group-hover:shadow-md"
                                    x-bind:class="{ '-translate-y-1 scale-105 text-rose-800 shadow-md ring-rose-200': isDragging }"
                                    aria-hidden="true"
                                >
                                    <x-lucide-cloud-upload class="size-8" />
                                </span>
                                <p class="mt-5 text-base font-black text-slate-900 sm:text-lg">
                                    <span x-show="! isDragging">Kéo và thả file Excel vào đây</span>
                                    <span x-cloak x-show="isDragging">Thả file để bắt đầu tải lên</span>
                                </p>
                                <p class="mt-2 text-sm text-slate-500">hoặc chọn file trực tiếp từ thiết bị của bạn</p>
                                <span class="pointer-events-auto mt-5 inline-flex items-center gap-2 rounded-lg bg-rose-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition group-hover:bg-rose-800">
                                    <x-lucide-folder-open class="size-4" aria-hidden="true" />
                                    Chọn tệp
                                </span>
                                <p id="excel-file-help" class="mt-4 text-xs font-medium text-slate-500">Hỗ trợ XLSX, XLS, CSV · Tối đa 10 MB</p>
                            </div>

                            <div x-cloak x-show="fileName" class="flex flex-col items-center">
                                <span class="grid size-16 place-items-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200" aria-hidden="true">
                                    <x-lucide-file-spreadsheet class="size-8" />
                                </span>
                                <p class="mt-5 text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                    <span x-show="! uploading">Tệp đã sẵn sàng</span>
                                    <span x-show="uploading">Đang tải tệp lên</span>
                                </p>
                                <p class="mt-2 max-w-full truncate text-base font-black text-slate-900 sm:text-lg" x-text="fileName"></p>
                                <p class="mt-1 text-sm text-slate-500" x-text="fileSize"></p>
                                <span class="pointer-events-auto mt-5 inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition group-hover:border-rose-300 group-hover:text-rose-800">
                                    <x-lucide-refresh-cw class="size-4" aria-hidden="true" />
                                    Chọn tệp khác
                                </span>
                            </div>

                            <div
                                x-cloak
                                x-show="uploading"
                                class="mx-auto mt-5 max-w-sm"
                                role="progressbar"
                                aria-label="Tiến độ tải tệp"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                x-bind:aria-valuenow="uploadProgress"
                            >
                                <div class="h-1.5 overflow-hidden rounded-full bg-rose-100">
                                    <div
                                        class="h-full rounded-full bg-rose-700 transition-[width] duration-150"
                                        x-bind:style="`width: ${uploadProgress}%`"
                                    ></div>
                                </div>
                            </div>

                            <p id="excel-file-status" class="sr-only" role="status" aria-live="polite" aria-atomic="true">
                                <span x-text="statusMessage"></span>
                            </p>
                        </div>
                    </label>

                    <p
                        x-cloak
                        x-show="uploadFeedback"
                        x-text="uploadFeedback"
                        class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-rose-700"
                    ></p>

                    @error('file')
                        <p id="excel-file-error" class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-rose-700">
                            <x-lucide-circle-alert class="size-4 shrink-0" aria-hidden="true" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="flex items-center gap-2 text-xs leading-5 text-slate-500">
                        <x-lucide-shield-check class="size-4 shrink-0 text-emerald-600" aria-hidden="true" />
                        File chỉ được dùng để nhập dữ liệu vào hệ thống.
                    </p>
                    <button
                        type="submit"
                        class="btn-primary shrink-0 px-6 py-3"
                        x-bind:disabled="uploading || resetting"
                        wire:loading.attr="disabled"
                        wire:target="import,file,clearFailedUpload"
                    >
                        <x-lucide-file-up class="size-4" wire:loading.remove wire:target="import" aria-hidden="true" />
                        <x-lucide-loader-circle class="size-4 motion-safe:animate-spin" wire:loading wire:target="import" aria-hidden="true" />
                        <span wire:loading.remove wire:target="import">Bắt đầu import</span>
                        <span wire:loading wire:target="import">Đang xử lý...</span>
                    </button>
                </div>
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
                        <div class="grid size-11 shrink-0 place-items-center rounded-2xl bg-[#e8f5ed] text-[#217346] ring-4 ring-[#f3f8f5]">
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
