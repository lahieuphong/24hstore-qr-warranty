<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\Admin\AdminActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Hồ sơ cá nhân')]
class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile(AdminActivityLogger $activityLogger): void
    {
        /** @var User $user */
        $user = Auth::user();
        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);
        $activityLogger->record(
            'profile.updated',
            'Cập nhật hồ sơ cá nhân '.$user->email.'.',
            $user,
        );
        session()->flash('success', 'Đã cập nhật hồ sơ.');
    }

    public function updatePassword(AdminActivityLogger $activityLogger): void
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
        ]);

        $user->update(['password' => $validated['password']]);
        $activityLogger->record(
            'profile.password_changed',
            'Đổi mật khẩu tài khoản '.$user->email.'.',
            $user,
        );
        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('success', 'Đã đổi mật khẩu.');
    }

    public function render(): View
    {
        return view('livewire.profile');
    }
}
