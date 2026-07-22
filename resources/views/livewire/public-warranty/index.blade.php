<div>
    <section class="public-lookup-hero relative isolate overflow-hidden">
        <img
            src="{{ asset('images/warranty-gears-hero-v1.webp') }}"
            alt=""
            class="public-lookup-hero-image absolute inset-0 -z-20 size-full object-cover"
            width="1715"
            height="917"
            fetchpriority="high"
        >
        <div class="public-lookup-hero-overlay absolute inset-0 -z-10" aria-hidden="true"></div>

        <div class="mx-auto flex min-h-[calc(100dvh-136px)] max-w-7xl items-center px-5 py-10 sm:px-6 sm:py-12 lg:px-8">
            <div class="w-full">
                <div class="mx-auto max-w-5xl text-center">
                    <h1 class="mx-auto max-w-4xl text-[2.15rem] font-bold leading-[1.1] tracking-[-0.03em] text-slate-950 sm:text-[2.6rem] lg:text-[2.9rem]">
                        Kiểm tra thông tin và thời gian bảo hành
                    </h1>
                    <p class="mx-auto mt-4 max-w-4xl text-sm leading-6 text-slate-600 sm:text-base sm:leading-7 lg:whitespace-nowrap">
                        Nhập IMEI hoặc quét mã QR trên sản phẩm để xem dữ liệu bảo hành được cập nhật trực tiếp từ hệ thống 24hStore.
                    </p>
                </div>

                <form wire:submit="search" class="public-search-panel mx-auto mt-7 max-w-4xl p-4 text-left sm:p-5">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <label for="imei" class="text-sm font-bold text-slate-800">Nhập IMEI sản phẩm</label>
                        <span class="hidden items-center gap-1.5 text-xs font-semibold text-slate-500 sm:inline-flex">
                            <x-lucide-lock-keyhole class="size-3.5 text-rose-700" aria-hidden="true" />
                            Tra cứu bảo mật
                        </span>
                    </div>

                    <div class="public-search-group">
                        <div class="relative min-w-0 flex-1">
                            <x-lucide-scan-line class="pointer-events-none absolute left-5 top-1/2 size-5 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                            <input
                                id="imei"
                                wire:model="imei"
                                autocomplete="off"
                                inputmode="text"
                                class="public-search-input font-mono"
                                placeholder="Vui lòng nhập số IMEI"
                            >
                        </div>
                        <button type="submit" wire:loading.attr="disabled" class="public-search-submit">
                            <x-lucide-search class="size-5" wire:loading.remove wire:target="search" aria-hidden="true" />
                            <x-lucide-loader-circle class="size-5 animate-spin" wire:loading wire:target="search" aria-hidden="true" />
                            <span wire:loading.remove wire:target="search">Tra cứu</span>
                            <span wire:loading wire:target="search">Đang tra cứu...</span>
                        </button>
                    </div>

                    @error('imei')
                        <p class="mt-3 flex items-start gap-2 text-xs font-semibold text-rose-700">
                            <x-lucide-circle-alert class="mt-0.5 size-3.5 shrink-0" aria-hidden="true" />
                            {{ $message }}
                        </p>
                    @enderror

                    @if ($lookupError)
                        <div class="mt-3 flex items-start gap-2 rounded-xl border px-4 py-3 text-sm font-semibold {{ $rateLimited ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                            @if ($rateLimited)
                                <x-lucide-clock-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            @else
                                <x-lucide-search-x class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            @endif
                            {{ $lookupError }}
                        </div>
                    @endif

                    <p class="mt-3 text-center text-xs leading-5 text-slate-500">
                        Bạn có thể nhập IMEI có hoặc không có khoảng trắng. Hệ thống chỉ sử dụng mã này để tra cứu thông tin bảo hành.
                    </p>
                </form>

                <div class="mt-6 flex flex-wrap justify-center gap-x-7 gap-y-3 text-xs font-semibold text-slate-600 sm:text-sm">
                    <span class="inline-flex items-center gap-2">
                        <x-lucide-zap class="size-4 text-rose-700" aria-hidden="true" />
                        Kết quả tức thời
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <x-lucide-database class="size-4 text-rose-700" aria-hidden="true" />
                        Dữ liệu tập trung
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <x-lucide-smartphone class="size-4 text-rose-700" aria-hidden="true" />
                        Tối ưu mọi thiết bị
                    </span>
                </div>
            </div>
        </div>
    </section>

    @if ($product && $showResultModal)
        @php
            $resultStatus = $product['warranty_status'] ?? 'unknown';
            $resultStatusClass = match ($resultStatus) {
                'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'expired' => 'bg-slate-100 text-slate-700 ring-slate-200',
                'replaced' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'locked' => 'bg-rose-50 text-rose-700 ring-rose-200',
                default => 'bg-slate-100 text-slate-600 ring-slate-200',
            };
        @endphp

        <div
            class="public-result-modal fixed inset-0 z-[100] grid place-items-center overflow-y-auto p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="warranty-result-title"
            aria-describedby="warranty-result-description"
            wire:key="warranty-result-modal-{{ $product['imei'] }}"
            x-data
            x-init="$nextTick(() => $refs.closeResult.focus())"
            x-on:keydown.escape.window="$wire.closeResultModal()"
            x-on:click.self="$wire.closeResultModal()"
        >
            <section class="public-result-dialog public-result-card w-full max-w-xl overflow-y-auto rounded-3xl border bg-white shadow-2xl">
                <div class="p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="grid size-12 shrink-0 place-items-center rounded-2xl border border-emerald-300 bg-emerald-50 text-emerald-700 ring-4 ring-emerald-50/80">
                            <x-lucide-shield-check class="size-6" aria-hidden="true" />
                        </div>
                        <button
                            type="button"
                            wire:click="closeResultModal"
                            x-ref="closeResult"
                            class="inline-flex size-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-rose-100"
                            aria-label="Đóng kết quả tra cứu"
                        >
                            <x-lucide-x class="size-5" aria-hidden="true" />
                        </button>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-700">Kết quả tra cứu</p>
                            <h2 id="warranty-result-title" class="mt-2 text-2xl font-bold leading-tight text-slate-950 sm:text-[1.7rem]">
                                {{ $product['name'] }}
                            </h2>
                        </div>
                        <span class="inline-flex w-fit shrink-0 rounded-full px-3 py-1.5 text-xs font-bold ring-1 {{ $resultStatusClass }}">
                            {{ $product['warranty_status_label'] }}
                        </span>
                    </div>

                    <div class="mt-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3.5">
                        <p class="text-xs font-bold uppercase tracking-wider text-rose-700">IMEI</p>
                        <p class="mt-1 break-all font-mono text-base font-bold text-slate-900">{{ $product['imei'] }}</p>
                    </div>

                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3.5">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mã hàng</dt>
                            <dd class="mt-1.5 break-words text-sm font-bold text-slate-900">{{ $product['product_code'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3.5">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Ngày nhập kho</dt>
                            <dd class="mt-1.5 text-sm font-bold text-slate-900">
                                {{ ! empty($product['warehouse_date']) ? \Illuminate\Support\Carbon::parse($product['warehouse_date'])->format('d/m/Y') : 'Không có' }}
                            </dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3.5">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Thời hạn</dt>
                            <dd class="mt-1.5 text-sm font-bold text-slate-900">
                                {{ ! empty($product['warranty_months']) ? $product['warranty_months'].' tháng' : 'Không xác định' }}
                            </dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3.5">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Ngày hết hạn</dt>
                            <dd class="mt-1.5 text-sm font-bold text-slate-900">
                                {{ ! empty($product['warranty_expires_at']) ? \Illuminate\Support\Carbon::parse($product['warranty_expires_at'])->format('d/m/Y') : 'Không có' }}
                            </dd>
                        </div>
                    </dl>

                    <p id="warranty-result-description" class="mt-4 flex items-start gap-2 text-xs font-medium leading-5 text-emerald-700">
                        <x-lucide-shield-check class="mt-0.5 size-4 shrink-0 text-emerald-600" aria-hidden="true" />
                        Dữ liệu được cập nhật tại thời điểm tra cứu. Thông tin nội bộ và tài khoản quản trị luôn được bảo vệ.
                    </p>

                </div>
            </section>
        </div>
    @endif
</div>
