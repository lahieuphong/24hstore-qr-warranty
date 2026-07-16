<div class="space-y-6">
    <x-flash />
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Tổng quan vận hành</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Hệ thống QR bảo hành</h1>
            <p class="mt-2 text-sm text-slate-500">Theo dõi nhanh dữ liệu IMEI, trạng thái bảo hành và hoạt động import.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('products.create')
                <a href="{{ route('admin.products.index') }}" class="btn-primary">Thêm sản phẩm</a>
            @endcan
            @can('products.import')
                <a href="{{ route('admin.imports.index') }}" class="btn-secondary">Import Excel</a>
            @endcan
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="card p-5">
            <p class="text-sm font-semibold text-slate-500">Tổng sản phẩm</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm font-semibold text-emerald-700">Còn bảo hành</p>
            <p class="mt-3 text-3xl font-black text-emerald-700">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm font-semibold text-slate-600">Hết bảo hành</p>
            <p class="mt-3 text-3xl font-black text-slate-700">{{ number_format($stats['expired']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm font-semibold text-amber-700">Đổi bảo hành</p>
            <p class="mt-3 text-3xl font-black text-amber-700">{{ number_format($stats['replaced']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm font-semibold text-rose-700">Khóa bảo hành</p>
            <p class="mt-3 text-3xl font-black text-rose-700">{{ number_format($stats['locked']) }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="card overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="font-black text-slate-900">Sản phẩm mới cập nhật</h2>
                    <p class="mt-1 text-xs text-slate-500">8 bản ghi gần nhất</p>
                </div>
                <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Xem tất cả</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Sản phẩm</th>
                            <th class="px-5 py-3">IMEI</th>
                            <th class="px-5 py-3">Trạng thái</th>
                            <th class="px-5 py-3">Ngày nhập</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($recentProducts as $product)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-900">{{ $product->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $product->product_code }}</p>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs font-semibold text-slate-700">{{ $product->imei }}</td>
                                <td class="px-5 py-4"><x-status-badge :status="$product->effectiveWarrantyStatus()" /></td>
                                <td class="px-5 py-4 text-slate-600">{{ $product->warehouse_date->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Chưa có sản phẩm.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-black text-slate-900">Import gần đây</h2>
                <p class="mt-1 text-xs text-slate-500">Theo dõi kết quả nạp dữ liệu</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentImports as $batch)
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $batch->original_filename }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $batch->user?->name ?? 'Hệ thống' }} · {{ $batch->created_at->format('d/m H:i') }}</p>
                            </div>
                            <span class="rounded-full px-2 py-1 text-xs font-bold {{ $batch->failed_rows ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                {{ $batch->success_rows }}/{{ $batch->total_rows }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="p-5 text-sm text-slate-500">Chưa có lịch sử import.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
