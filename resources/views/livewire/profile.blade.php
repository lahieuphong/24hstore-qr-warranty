<div class="mx-auto max-w-4xl space-y-6">
    <x-flash />
    <div>
        <p class="text-sm font-semibold text-rose-700">Tài khoản</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Hồ sơ cá nhân</h1>
        <p class="mt-2 text-sm text-slate-500">Cập nhật thông tin hiển thị và mật khẩu đăng nhập.</p>
    </div>

    @if ($environmentAdminLocked)
        <section class="card flex items-start gap-4 border-amber-200 bg-amber-50 p-6" aria-labelledby="environment-admin-lock-title">
            <div class="grid size-11 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-700">
                <x-lucide-lock-keyhole class="size-5" aria-hidden="true" />
            </div>
            <div>
                <h2 id="environment-admin-lock-title" class="font-black text-amber-950">Hồ sơ được quản lý từ Environment</h2>
                <p class="mt-1 text-sm leading-6 text-amber-900">
                    Tên, email và mật khẩu của tài khoản này được đồng bộ khi hệ thống khởi động. Hãy cập nhật các biến ADMIN trên Render rồi redeploy để thay đổi thông tin đăng nhập.
                </p>
            </div>
        </section>
    @else
        <div class="grid gap-6 lg:grid-cols-2">
        <form wire:submit="updateProfile" class="card p-6">
            <h2 class="text-lg font-black text-slate-900">Thông tin cơ bản</h2>
            <div class="mt-5 space-y-4">
                <div>
                    <label class="form-label" for="profile-name">Họ tên</label>
                    <input id="profile-name" wire:model="name" class="form-input" autocomplete="name">
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label" for="profile-email">Email</label>
                    <input id="profile-email" type="email" wire:model="email" class="form-input" autocomplete="email">
                    @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button class="btn-primary" type="submit" wire:loading.attr="disabled">Lưu hồ sơ</button>
            </div>
        </form>

        <form wire:submit="updatePassword" class="card p-6">
            <h2 class="text-lg font-black text-slate-900">Đổi mật khẩu</h2>
            <div class="mt-5 space-y-4">
                <div>
                    <label class="form-label" for="current-password">Mật khẩu hiện tại</label>
                    <x-password-input id="current-password" wire:model="current_password" autocomplete="current-password" />
                    @error('current_password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label" for="new-password">Mật khẩu mới</label>
                    <x-password-input id="new-password" wire:model="password" autocomplete="new-password" />
                    @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label" for="password-confirmation">Nhập lại mật khẩu mới</label>
                    <x-password-input id="password-confirmation" wire:model="password_confirmation" autocomplete="new-password" />
                </div>
                <button class="btn-primary" type="submit" wire:loading.attr="disabled">Đổi mật khẩu</button>
            </div>
        </form>
        </div>
    @endif
</div>
