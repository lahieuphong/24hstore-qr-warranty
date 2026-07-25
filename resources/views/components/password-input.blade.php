@props(['id'])

<div class="relative" data-password-field>
    <input
        id="{{ $id }}"
        type="password"
        {{ $attributes->class(['form-input', 'form-input-trailing-icon']) }}
    >
    <button
        type="button"
        data-password-toggle
        aria-controls="{{ $id }}"
        aria-label="Hiện mật khẩu"
        aria-pressed="false"
        title="Hiện mật khẩu"
        class="absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center rounded-r-md text-slate-400 transition hover:text-rose-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-rose-300"
    >
        <x-lucide-eye data-password-eye-open class="size-5" aria-hidden="true" />
        <x-lucide-eye-off data-password-eye-closed class="hidden size-5" aria-hidden="true" />
    </button>
</div>
