<div class="space-y-6">
    <x-flash />
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Bảo mật nội bộ</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Người dùng và phân quyền</h1>
            <p class="mt-2 text-sm text-slate-500">Tạo tài khoản nhân viên, gán vai trò và khóa quyền truy cập khi cần.</p>
        </div>
        <button type="button" wire:click="create" class="btn-primary">+ Thêm người dùng</button>
    </div>

    <section class="card p-4 sm:p-5">
        <input type="search" wire:model.live.debounce.350ms="search" class="form-input" placeholder="Tìm theo họ tên hoặc email...">
    </section>

    <section class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Người dùng</th>
                        <th class="px-5 py-3">Vai trò</th>
                        <th class="px-5 py-3">Trạng thái</th>
                        <th class="px-5 py-3">Ngày tạo</th>
                        <th class="px-5 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-slate-100 font-black text-slate-700">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $user->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
                                    {{ $user->roles->first()?->name ?? 'Chưa gán' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <button type="button" wire:click="edit({{ $user->id }})" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Sửa</button>
                                    <button type="button" wire:click="toggleActive({{ $user->id }})" class="rounded-lg border px-3 py-2 text-xs font-bold {{ $user->is_active ? 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                        {{ $user->is_active ? 'Khóa' : 'Mở khóa' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Không tìm thấy người dùng.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $users->links() }}</div>
        @endif
    </section>

    @if ($showForm)
        <div class="fixed inset-0 z-[70] overflow-y-auto bg-slate-950/50 p-4" role="dialog" aria-modal="true">
            <div class="mx-auto my-8 max-w-2xl rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">{{ $editingId ? 'Cập nhật người dùng' : 'Thêm người dùng' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Mỗi tài khoản được gán một vai trò nghiệp vụ.</p>
                    </div>
                    <button type="button" wire:click="closeForm" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit="save" class="p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="user-name" class="form-label">Họ tên <span class="text-rose-600">*</span></label>
                            <input id="user-name" wire:model="name" class="form-input">
                            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="user-email" class="form-label">Email <span class="text-rose-600">*</span></label>
                            <input id="user-email" type="email" wire:model="email" class="form-input">
                            @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="user-role" class="form-label">Vai trò <span class="text-rose-600">*</span></label>
                            <select id="user-role" wire:model="role" class="form-input">
                                @foreach ($roles as $roleName)
                                    <option value="{{ $roleName }}">{{ $roleName }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700">
                                <input type="checkbox" wire:model="is_active" class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                Tài khoản đang hoạt động
                            </label>
                            @error('is_active') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="user-password" class="form-label">Mật khẩu {{ $editingId ? '(để trống nếu không đổi)' : '*' }}</label>
                            <input id="user-password" type="password" wire:model="password" class="form-input" autocomplete="new-password">
                            @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="user-password-confirm" class="form-label">Nhập lại mật khẩu</label>
                            <input id="user-password-confirm" type="password" wire:model="password_confirmation" class="form-input" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closeForm" class="btn-secondary">Hủy</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">Lưu tài khoản</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
