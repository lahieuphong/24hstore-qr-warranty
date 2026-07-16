<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Tra cứu bảo hành - {{ $product->imei }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
    <main class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-10">
        <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">
            <div class="bg-slate-950 px-6 py-7 text-white sm:px-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300">24hStore</p>
                        <h1 class="mt-2 text-2xl font-black">Thông tin bảo hành</h1>
                        <p class="mt-2 text-sm text-slate-300">Dữ liệu tra cứu từ mã QR trên sản phẩm.</p>
                    </div>
                    <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-indigo-500 font-black">QR</div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <div class="mb-7 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-slate-50 p-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tình trạng bảo hành</p>
                        <p class="mt-1 text-lg font-black text-slate-900">{{ $status->label() }}</p>
                    </div>
                    <x-status-badge :status="$status" class="text-sm" />
                </div>

                <dl class="divide-y divide-slate-100">
                    <div class="grid gap-1 py-4 sm:grid-cols-5 sm:gap-4">
                        <dt class="text-sm font-semibold text-slate-500 sm:col-span-2">Mã hàng</dt>
                        <dd class="break-words text-sm font-bold text-slate-900 sm:col-span-3">{{ $product->product_code }}</dd>
                    </div>
                    <div class="grid gap-1 py-4 sm:grid-cols-5 sm:gap-4">
                        <dt class="text-sm font-semibold text-slate-500 sm:col-span-2">Tên hàng</dt>
                        <dd class="break-words text-sm font-bold text-slate-900 sm:col-span-3">{{ $product->name }}</dd>
                    </div>
                    <div class="grid gap-1 py-4 sm:grid-cols-5 sm:gap-4">
                        <dt class="text-sm font-semibold text-slate-500 sm:col-span-2">IMEI</dt>
                        <dd class="break-all font-mono text-sm font-bold text-slate-900 sm:col-span-3">{{ $product->imei }}</dd>
                    </div>
                    <div class="grid gap-1 py-4 sm:grid-cols-5 sm:gap-4">
                        <dt class="text-sm font-semibold text-slate-500 sm:col-span-2">Ngày nhập kho</dt>
                        <dd class="text-sm font-bold text-slate-900 sm:col-span-3">{{ $product->warehouse_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="grid gap-1 py-4 sm:grid-cols-5 sm:gap-4">
                        <dt class="text-sm font-semibold text-slate-500 sm:col-span-2">Thời hạn bảo hành</dt>
                        <dd class="text-sm font-bold text-slate-900 sm:col-span-3">{{ $product->warranty_months ? $product->warranty_months.' tháng' : 'Không ghi nhận' }}</dd>
                    </div>
                    <div class="grid gap-1 py-4 sm:grid-cols-5 sm:gap-4">
                        <dt class="text-sm font-semibold text-slate-500 sm:col-span-2">Ngày hết hạn</dt>
                        <dd class="text-sm font-bold text-slate-900 sm:col-span-3">{{ $product->warranty_expires_at?->format('d/m/Y') ?? 'Không có' }}</dd>
                    </div>
                </dl>

                <div class="mt-7 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm leading-6 text-indigo-900">
                    Thông tin này chỉ phục vụ kiểm tra sản phẩm và bảo hành. Khi cần hỗ trợ, hãy cung cấp IMEI cho nhân viên phụ trách.
                </div>
            </div>
        </section>
    </main>
</body>
</html>
