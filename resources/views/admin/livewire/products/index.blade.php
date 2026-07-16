{{-- Admin product management screen. --}}
<div class="space-y-6">
    <x-flash />
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Kho & bảo hành</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Sản phẩm và mã QR</h1>
            <p class="mt-2 text-sm text-slate-500">Mỗi IMEI có một QR riêng, dẫn đến trang tra cứu công khai bằng mã token ngẫu nhiên.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('products.import')
                <a href="{{ route('admin.imports.index') }}" class="btn-secondary">Import Excel</a>
            @endcan
            @can('products.create')
                <button type="button" wire:click="create" class="btn-primary">+ Thêm sản phẩm</button>
            @endcan
        </div>
    </div>

    <section class="card p-4 sm:p-5">
        <div class="grid gap-3 md:grid-cols-12">
            <div class="md:col-span-6">
                <label for="product-search" class="sr-only">Tìm kiếm</label>
                <input
                    id="product-search"
                    type="search"
                    wire:model.live.debounce.350ms="search"
                    class="form-input"
                    placeholder="Tìm theo IMEI, mã hàng hoặc tên sản phẩm..."
                >
            </div>
            <div class="md:col-span-3">
                <label for="product-status" class="sr-only">Trạng thái</label>
                <select id="product-status" wire:model.live="status" class="form-input">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <label for="product-per-page" class="sr-only">Số dòng</label>
                <select id="product-per-page" wire:model.live="perPage" class="form-input">
                    <option value="10">10 dòng / trang</option>
                    <option value="20">20 dòng / trang</option>
                    <option value="50">50 dòng / trang</option>
                    <option value="100">100 dòng / trang</option>
                </select>
            </div>
        </div>
    </section>

    @if (count($selected) > 0)
        <div class="flex flex-col gap-3 rounded-2xl border border-indigo-200 bg-indigo-50 p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-semibold text-indigo-900">Đã chọn {{ count($selected) }} sản phẩm.</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="clearSelection" class="btn-secondary">Bỏ chọn</button>
                @can('products.print')
                    <a
                        href="{{ route('admin.labels.bulk', ['ids' => implode(',', $selected)]) }}"
                        target="_blank"
                        rel="noopener"
                        class="btn-primary"
                    >
                        Xuất tem PDF
                    </a>
                @endcan
            </div>
        </div>
    @endif

    <section class="card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-5">
            <p class="text-sm text-slate-500">
                Kết quả: <span class="font-bold text-slate-800">{{ number_format($products->total()) }}</span> sản phẩm
            </p>
            <button type="button" wire:click="selectCurrentPage" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                Chọn các dòng trang này
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="w-12 px-4 py-3"><span class="sr-only">Chọn</span></th>
                        <th class="min-w-64 px-4 py-3">
                            <button type="button" wire:click="sortBy('name')" class="hover:text-slate-900">Sản phẩm ↕</button>
                        </th>
                        <th class="min-w-44 px-4 py-3">
                            <button type="button" wire:click="sortBy('imei')" class="hover:text-slate-900">IMEI ↕</button>
                        </th>
                        <th class="min-w-32 px-4 py-3">
                            <button type="button" wire:click="sortBy('warehouse_date')" class="hover:text-slate-900">Nhập kho ↕</button>
                        </th>
                        <th class="min-w-40 px-4 py-3">Bảo hành</th>
                        <th class="min-w-36 px-4 py-3">Trạng thái</th>
                        <th class="min-w-56 px-4 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($products as $product)
                        <tr wire:key="product-{{ $product->id }}" class="align-top hover:bg-slate-50/70">
                            <td class="px-4 py-4">
                                <input
                                    type="checkbox"
                                    wire:model.live="selected"
                                    value="{{ $product->id }}"
                                    class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    aria-label="Chọn {{ $product->imei }}"
                                >
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-bold text-slate-900">{{ $product->name }}</p>
                                <p class="mt-1 text-xs font-semibold text-indigo-600">{{ $product->product_code }}</p>
                                @if ($product->creator)
                                    <p class="mt-2 text-xs text-slate-400">Tạo bởi {{ $product->creator->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <p class="break-all font-mono text-xs font-bold text-slate-800">{{ $product->imei }}</p>
                                <p class="mt-2 text-xs text-slate-400">QR token riêng</p>
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                <p class="font-semibold">{{ $product->warehouse_date->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                <p class="font-semibold">{{ $product->warranty_months ? $product->warranty_months.' tháng' : 'Không ghi nhận' }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Hết hạn: {{ $product->warranty_expires_at?->format('d/m/Y') ?? 'Không có' }}
                                </p>
                            </td>
                            <td class="px-4 py-4">
                                <x-status-badge :status="$product->effectiveWarrantyStatus()" />
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button" wire:click="showQr({{ $product->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                        Xem QR
                                    </button>
                                    @can('products.print')
                                        <a href="{{ route('admin.products.label', $product) }}" target="_blank" rel="noopener" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100">
                                            Tem PDF
                                        </a>
                                    @endcan
                                    @can('products.update')
                                        <button type="button" wire:click="edit({{ $product->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                            Sửa
                                        </button>
                                    @endcan
                                    @can('products.delete')
                                        <button type="button" wire:click="confirmDelete({{ $product->id }})" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100">
                                            Xóa
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <p class="font-bold text-slate-700">Không tìm thấy sản phẩm.</p>
                                <p class="mt-1 text-sm text-slate-500">Thử đổi từ khóa hoặc bộ lọc trạng thái.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="border-t border-slate-200 px-4 py-4 sm:px-5">
                {{ $products->links() }}
            </div>
        @endif
    </section>

    @if ($showForm)
        <div class="fixed inset-0 z-[70] overflow-y-auto bg-slate-950/50 p-4" role="dialog" aria-modal="true">
            <div class="mx-auto my-6 max-w-3xl rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">{{ $editingId ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">QR được tạo tự động; IMEI không được trùng.</p>
                    </div>
                    <button type="button" wire:click="closeForm" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100" aria-label="Đóng">✕</button>
                </div>

                <form wire:submit="save" class="p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="product-code" class="form-label">Mã hàng <span class="text-rose-600">*</span></label>
                            <input id="product-code" wire:model="product_code" class="form-input" placeholder="IP15-128-BLK">
                            @error('product_code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="product-imei" class="form-label">IMEI <span class="text-rose-600">*</span></label>
                            <input id="product-imei" wire:model="imei" class="form-input font-mono" placeholder="012345678901234">
                            @error('imei') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="product-name" class="form-label">Tên hàng <span class="text-rose-600">*</span></label>
                            <input id="product-name" wire:model="name" class="form-input" placeholder="Tên sản phẩm">
                            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="warehouse-date" class="form-label">Ngày nhập kho <span class="text-rose-600">*</span></label>
                            <input id="warehouse-date" type="date" wire:model="warehouse_date" class="form-input">
                            @error('warehouse_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="warranty-months" class="form-label">Thời hạn bảo hành (tháng)</label>
                            <input id="warranty-months" type="number" min="1" max="120" wire:model="warranty_months" class="form-input" placeholder="12">
                            @error('warranty_months') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="warranty-status" class="form-label">Trạng thái bảo hành <span class="text-rose-600">*</span></label>
                            <select id="warranty-status" wire:model="warranty_status" class="form-input">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('warranty_status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="internal-note" class="form-label">Ghi chú nội bộ</label>
                            <textarea id="internal-note" wire:model="internal_note" rows="4" class="form-input" placeholder="Chỉ nhân viên quản trị nhìn thấy..."></textarea>
                            @error('internal_note') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs leading-5 text-slate-600">
                        Ngày hết hạn được tính tự động từ ngày nhập kho và số tháng bảo hành. Khi quá hạn, trang tra cứu sẽ hiển thị “Hết bảo hành” kể cả trước khi tác vụ đồng bộ ban đêm chạy.
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closeForm" class="btn-secondary">Hủy</button>
                        <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                            <span wire:loading.remove wire:target="save">Lưu sản phẩm</span>
                            <span wire:loading wire:target="save">Đang lưu...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[70] grid place-items-center bg-slate-950/50 p-4" role="dialog" aria-modal="true">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
                <h2 class="text-xl font-black text-slate-900">Xóa sản phẩm?</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Bản ghi sẽ được xóa mềm để giữ lịch sử và ngăn tái sử dụng IMEI đã có.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="btn-secondary">Hủy</button>
                    <button type="button" wire:click="delete" wire:loading.attr="disabled" class="btn-danger">Xóa sản phẩm</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showQrModal && $qrProduct)
        <div class="fixed inset-0 z-[70] overflow-y-auto bg-slate-950/50 p-4" role="dialog" aria-modal="true">
            <div class="mx-auto my-8 w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">QR tra cứu bảo hành</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $qrProduct->name }} · {{ $qrProduct->imei }}</p>
                    </div>
                    <button type="button" wire:click="closeQr" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100" aria-label="Đóng">✕</button>
                </div>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 text-center">
                    <img src="{{ route('admin.products.qr', $qrProduct) }}" alt="QR {{ $qrProduct->imei }}" class="mx-auto size-72 max-w-full">
                </div>

                <div class="mt-4 rounded-xl bg-slate-100 p-3">
                    <p class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">Đường dẫn QR</p>
                    <p class="break-all font-mono text-xs text-slate-700">{{ $qrProduct->publicLookupUrl() }}</p>
                </div>

                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button
                        type="button"
                        class="btn-secondary"
                        x-on:click="navigator.clipboard.writeText(@js($qrProduct->publicLookupUrl()))"
                    >
                        Sao chép link
                    </button>
                    @can('products.print')
                        <a href="{{ route('admin.products.label', $qrProduct) }}" target="_blank" rel="noopener" class="btn-primary">Tải tem PDF</a>
                    @endcan
                </div>
            </div>
        </div>
    @endif
</div>
