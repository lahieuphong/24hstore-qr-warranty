<div class="public-detail-page px-5 py-10 sm:px-6 sm:py-14 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-240px)] max-w-3xl items-center">
        <div class="w-full space-y-5">
            @if ($product)
                <div class="text-center">
                    <span class="public-eyebrow inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-xs font-bold uppercase tracking-[0.16em]">
                        <x-lucide-badge-check class="size-4" aria-hidden="true" />
                        Mã QR hợp lệ
                    </span>
                    <h1 class="mt-5 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Thông tin bảo hành sản phẩm</h1>
                    <p class="mt-3 text-sm text-slate-600">Kết quả được cập nhật trực tiếp từ hệ thống bảo hành.</p>
                </div>

                <x-warranty-card :product="$product" />

                <div class="flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ route('warranty.check') }}" class="public-btn-secondary">
                        <x-lucide-search class="size-4" aria-hidden="true" />
                        Tra cứu IMEI khác
                    </a>
                    <button type="button" wire:click="reload" wire:loading.attr="disabled" class="public-btn-primary">
                        <x-lucide-refresh-cw class="size-4" wire:loading.class="animate-spin" wire:target="reload" aria-hidden="true" />
                        <span wire:loading.remove wire:target="reload">Làm mới dữ liệu</span>
                        <span wire:loading wire:target="reload">Đang tải...</span>
                    </button>
                </div>
            @else
                <section class="public-card p-6 text-center sm:p-10">
                    <div class="mx-auto grid size-16 place-items-center rounded-full {{ $state === 404 ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-700' }}">
                        @if ($state === 404)
                            <x-lucide-search-x class="size-8" aria-hidden="true" />
                        @else
                            <x-lucide-clock-alert class="size-8" aria-hidden="true" />
                        @endif
                    </div>
                    <h1 class="mt-5 text-2xl font-black text-slate-900">{{ $state === 404 ? 'Không tìm thấy sản phẩm' : 'Vui lòng thử lại sau' }}</h1>
                    <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-slate-500">{{ $message }}</p>
                    <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                        <a href="{{ route('warranty.check') }}" class="public-btn-secondary">
                            <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                            Về trang tra cứu
                        </a>
                        @if ($state === 429)
                            <button type="button" wire:click="reload" wire:loading.attr="disabled" class="public-btn-primary">
                                <x-lucide-refresh-cw class="size-4" wire:loading.class="animate-spin" wire:target="reload" aria-hidden="true" />
                                <span wire:loading.remove wire:target="reload">Thử lại</span>
                                <span wire:loading wire:target="reload">Đang thử lại...</span>
                            </button>
                        @endif
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>
