<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.admin')]
#[Title('Người dùng & phân quyền')]
class Index extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = 'viewer';

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('users.manage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('users.manage');
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('users.manage');
        $user = User::query()->findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()?->name ?? 'viewer';
        $this->is_active = $user->is_active;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showForm = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('users.manage');
        $user = $this->editingId ? User::query()->findOrFail($this->editingId) : new User;
        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:10', 'confirmed'],
            'role' => [
                'required',
                Rule::exists('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
            'is_active' => ['boolean'],
        ]);

        $removesActiveSuperAdmin = $user->exists
            && $user->hasRole('super-admin')
            && ($validated['role'] !== 'super-admin' || ! $validated['is_active']);

        if ($removesActiveSuperAdmin && ! $this->hasAnotherActiveSuperAdmin($user)) {
            $this->addError('role', 'Hệ thống phải còn ít nhất một super-admin đang hoạt động.');

            return;
        }

        if ($user->is(auth()->user()) && ! $validated['is_active']) {
            $this->addError('is_active', 'Bạn không thể khóa chính tài khoản đang đăng nhập.');

            return;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_active = $validated['is_active'];

        if (! empty($validated['password'] ?? null)) {
            $user->password = $validated['password'];
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Đã lưu tài khoản và vai trò.');
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('users.manage');
        $user = User::query()->findOrFail($id);

        if ($user->is(auth()->user())) {
            session()->flash('warning', 'Không thể khóa chính tài khoản đang đăng nhập.');

            return;
        }

        if ($user->hasRole('super-admin') && $user->is_active && ! $this->hasAnotherActiveSuperAdmin($user)) {
            session()->flash('warning', 'Hệ thống phải còn ít nhất một super-admin đang hoạt động.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        session()->flash('success', $user->is_active ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function render(): View
    {
        $users = User::query()
            ->with('roles:id,name')
            ->when($this->search !== '', function ($query): void {
                $needle = "%{$this->search}%";
                $query->where(function ($query) use ($needle): void {
                    $query->whereLike('name', $needle)->orWhereLike('email', $needle);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.users.index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }

    private function hasAnotherActiveSuperAdmin(User $excluded): bool
    {
        return User::query()
            ->where('is_active', true)
            ->where('id', '!=', $excluded->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'super-admin'))
            ->exists();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'email',
            'password',
            'password_confirmation',
        ]);
        $this->role = 'viewer';
        $this->is_active = true;
        $this->resetValidation();
    }
}
