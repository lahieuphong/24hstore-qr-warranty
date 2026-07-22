<div class="grid min-h-[calc(100vh-190px)] items-center gap-10 py-8 lg:grid-cols-[minmax(0,1fr)_520px]">
    <section class="text-white">
        <span class="inline-flex rounded-full border border-indigo-300/20 bg-indigo-400/10 px-3 py-1.5 text-xs font-bold text-indigo-100 backdrop-blur">QR Warranty Portal</span>
        <h1 class="mt-6 max-w-3xl text-4xl font-black tracking-tight sm:text-5xl lg:text-6xl">Kiểm tra thông tin bảo hành nhanh theo IMEI.</h1>
        <p class="mt-5 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">Quét mã QR trên sản phẩm hoặc nhập IMEI để xem thông tin bảo hành được cập nhật trực tiếp từ hệ thống.</p>
        <div class="mt-8 grid max-w-2xl gap-3 text-sm text-slate-200 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                <p class="font-black text-white">01 QR / IMEI</p>
                <p class="mt-1 text-xs text-slate-400">Không trùng dữ liệu</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                <p class="font-black text-white">Tra cứu tức thời</p>
                <p class="mt-1 text-xs text-slate-400">Tối ưu điện thoại</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                <p class="font-black text-white">Dữ liệu tập trung</p>
                <p class="mt-1 text-xs text-slate-400">Cùng hệ thống quản trị</p>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        <form wire:submit="search" class="public-card p-5 sm:p-7">
            <p class="text-sm font-bold text-indigo-600">Tra cứu thủ công</p>
            <h2 class="mt-1 text-2xl font-black text-slate-900">Nhập IMEI sản phẩm</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Bạn có thể nhập IMEI có hoặc không có khoảng trắng.</p>

            <div class="mt-6">
                <label for="imei" class="mb-2 block text-sm font-bold text-slate-700">IMEI</label>
                <input id="imei" wire:model="imei" autocomplete="off" inputmode="text" class="public-form-input font-mono" placeholder="012345678901234">
                @error('imei')
                    <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            @if ($lookupError)
                <div class="mt-4 rounded-2xl border px-4 py-3 text-sm font-semibold {{ $rateLimited ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                    {{ $lookupError }}
                </div>
            @endif

            <button type="submit" wire:loading.attr="disabled" class="public-btn-primary mt-5 w-full">
                <x-lucide-search class="size-4" wire:loading.remove wire:target="search" aria-hidden="true" />
                <x-lucide-loader-circle class="size-4 animate-spin" wire:loading wire:target="search" aria-hidden="true" />
                <span wire:loading.remove wire:target="search">Tra cứu bảo hành</span>
                <span wire:loading wire:target="search">Đang tra cứu...</span>
            </button>
        </form>

        @if ($product)
            <x-warranty-card :product="$product" />
        @endif
    </section>
</div>
