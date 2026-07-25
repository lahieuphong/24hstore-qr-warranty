<?php

namespace Tests\Feature;

use App\Livewire\Profile;
use App\Livewire\Users\Index as UserIndex;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class EnvironmentAdminEditingTest extends TestCase
{
    use RefreshDatabase;

    public function test_environment_admin_editing_is_hidden_and_rejected_when_the_flag_is_off(): void
    {
        $this->seed(RolePermissionSeeder::class);
        config(['admin.environment_admin_editable' => false]);

        $actor = User::factory()->create(['name' => 'Super admin thao tác']);
        $actor->assignRole('super-admin');
        $environmentAdmin = User::factory()->create([
            'name' => 'Quản trị hệ thống',
            'email' => 'environment-admin@example.com',
            'is_environment_admin' => true,
        ]);
        $environmentAdmin->assignRole('super-admin');
        $regularUser = User::factory()->create(['name' => 'Tài khoản thông thường']);

        $this->actingAs($actor)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee('wire:click="edit('.$environmentAdmin->id.')"', false)
            ->assertSee('wire:click="toggleActive('.$environmentAdmin->id.')"', false)
            ->assertSee('wire:click="edit('.$regularUser->id.')"', false);

        Livewire::test(UserIndex::class)
            ->call('edit', $environmentAdmin->id)
            ->assertForbidden();

        Livewire::test(UserIndex::class)
            ->set('editingId', $environmentAdmin->id)
            ->set('name', 'Tên đã bị chặn')
            ->set('email', 'blocked@example.com')
            ->set('role', 'super-admin')
            ->set('is_active', true)
            ->call('save')
            ->assertForbidden();

        $environmentAdmin->refresh();
        $this->assertSame('Quản trị hệ thống', $environmentAdmin->name);
        $this->assertSame('environment-admin@example.com', $environmentAdmin->email);
    }

    public function test_environment_admin_editing_is_available_when_the_flag_is_on(): void
    {
        $this->seed(RolePermissionSeeder::class);
        config(['admin.environment_admin_editable' => true]);

        $actor = User::factory()->create();
        $actor->assignRole('super-admin');
        $environmentAdmin = User::factory()->create([
            'name' => 'Quản trị hệ thống',
            'is_environment_admin' => true,
        ]);
        $environmentAdmin->assignRole('super-admin');

        $this->actingAs($actor)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('wire:click="edit('.$environmentAdmin->id.')"', false);

        Livewire::test(UserIndex::class)
            ->call('edit', $environmentAdmin->id)
            ->assertSet('editingId', $environmentAdmin->id)
            ->assertSet('showForm', true)
            ->set('name', 'Quản trị được phép sửa')
            ->set('email', 'editable-admin@example.com')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $environmentAdmin->refresh();
        $this->assertSame('Quản trị được phép sửa', $environmentAdmin->name);
        $this->assertSame('editable-admin@example.com', $environmentAdmin->email);
    }

    public function test_environment_admin_cannot_bypass_the_lock_through_its_profile(): void
    {
        $this->seed(RolePermissionSeeder::class);
        config(['admin.environment_admin_editable' => false]);

        $environmentAdmin = User::factory()->create([
            'name' => 'Quản trị hệ thống',
            'email' => 'environment-admin@example.com',
            'password' => 'current-password',
            'is_environment_admin' => true,
        ]);
        $environmentAdmin->assignRole('super-admin');
        $this->actingAs($environmentAdmin);

        Livewire::test(Profile::class)
            ->assertSee('Hồ sơ được quản lý từ Environment')
            ->assertDontSee('wire:submit="updateProfile"', false)
            ->set('name', 'Tên đã bị chặn')
            ->set('email', 'blocked-profile@example.com')
            ->call('updateProfile')
            ->assertForbidden();

        Livewire::test(Profile::class)
            ->set('current_password', 'current-password')
            ->set('password', 'replacement-password')
            ->set('password_confirmation', 'replacement-password')
            ->call('updatePassword')
            ->assertForbidden();

        $environmentAdmin->refresh();
        $this->assertSame('Quản trị hệ thống', $environmentAdmin->name);
        $this->assertSame('environment-admin@example.com', $environmentAdmin->email);
        $this->assertTrue(Hash::check('current-password', $environmentAdmin->password));
    }

    public function test_environment_admin_can_update_its_profile_when_the_flag_is_on(): void
    {
        $this->seed(RolePermissionSeeder::class);
        config(['admin.environment_admin_editable' => true]);

        $environmentAdmin = User::factory()->create([
            'name' => 'Quản trị hệ thống',
            'email' => 'environment-admin@example.com',
            'password' => 'current-password',
            'is_environment_admin' => true,
        ]);
        $environmentAdmin->assignRole('super-admin');
        $this->actingAs($environmentAdmin);

        Livewire::test(Profile::class)
            ->assertSee('wire:submit="updateProfile"', false)
            ->set('name', 'Quản trị đã cập nhật')
            ->set('email', 'updated-environment-admin@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        Livewire::test(Profile::class)
            ->set('current_password', 'current-password')
            ->set('password', 'replacement-password')
            ->set('password_confirmation', 'replacement-password')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $environmentAdmin->refresh();
        $this->assertSame('Quản trị đã cập nhật', $environmentAdmin->name);
        $this->assertSame('updated-environment-admin@example.com', $environmentAdmin->email);
        $this->assertTrue(Hash::check('replacement-password', $environmentAdmin->password));
    }

    public function test_render_production_example_keeps_environment_admin_editing_disabled(): void
    {
        $this->assertStringContainsString(
            'ADMIN_ENVIRONMENT_ADMIN_EDITABLE=false',
            File::get(base_path('deploy/render.env.example')),
        );
    }
}
