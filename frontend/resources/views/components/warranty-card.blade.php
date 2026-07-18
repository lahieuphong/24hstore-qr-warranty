@props(['product'])

@php
    $status = $product['warranty_status'] ?? 'unknown';
    $statusClass = match ($status) {
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'expired' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'replaced' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'locked' => 'bg-rose-50 text-rose-700 ring-rose-200',
        default => 'bg-slate-100 text-slate-600 ring-slate-200',
    };
@endphp

<section class="public-card overflow-hidden">
    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-7">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">Kết quả tra cứu</p>
                <h2 class="mt-2 text-xl font-black text-slate-900 sm:text-2xl">{{ $product['name'] }}</h2>
            </div>
            <span class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-black ring-1 {{ $statusClass }}">{{ $product['warranty_status_label'] }}</span>
        </div>
    </div>

    <dl class="grid divide-y divide-slate-100 px-5 sm:grid-cols-2 sm:divide-x sm:divide-y-0 sm:px-0">
        <div class="py-5 sm:px-7">
            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Mã hàng</dt>
            <dd class="mt-2 font-bold text-slate-900">{{ $product['product_code'] }}</dd>
        </div>
        <div class="py-5 sm:px-7">
            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">IMEI</dt>
            <dd class="mt-2 break-all font-mono text-sm font-bold text-slate-900">{{ $product['imei'] }}</dd>
        </div>
    </dl>

    <dl class="grid gap-px bg-slate-200 sm:grid-cols-3">
        <div class="bg-white p-5 sm:p-6">
            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Ngày nhập kho</dt>
            <dd class="mt-2 text-sm font-bold text-slate-900">{{ !empty($product['warehouse_date']) ? \Illuminate\Support\Carbon::parse($product['warehouse_date'])->format('d/m/Y') : 'Không có' }}</dd>
        </div>
        <div class="bg-white p-5 sm:p-6">
            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Thời hạn</dt>
            <dd class="mt-2 text-sm font-bold text-slate-900">{{ !empty($product['warranty_months']) ? $product['warranty_months'].' tháng' : 'Không xác định' }}</dd>
        </div>
        <div class="bg-white p-5 sm:p-6">
            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Ngày hết hạn</dt>
            <dd class="mt-2 text-sm font-bold text-slate-900">{{ !empty($product['warranty_expires_at']) ? \Illuminate\Support\Carbon::parse($product['warranty_expires_at'])->format('d/m/Y') : 'Không có' }}</dd>
        </div>
    </dl>

    <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 text-xs leading-5 text-slate-500 sm:px-7">Dữ liệu được đọc từ backend tại thời điểm tra cứu. Ghi chú nội bộ và tài khoản quản trị không được gửi ra frontend.</div>
</section>
