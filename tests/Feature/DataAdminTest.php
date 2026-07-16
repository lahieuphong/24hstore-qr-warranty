<?php

namespace Tests\Feature;

use App\Livewire\DataAdmin\Index;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DataAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/data')
            ->assertRedirect(route('login'));
    }

    public function test_viewer_is_forbidden_from_accessing_data_admin(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)
            ->get(route('admin.data.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_access_data_admin(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.data.index'))
            ->assertOk();
    }

    public function test_super_admin_can_select_whitelisted_tables_and_search_rows(): void
    {
        $admin = $this->createSuperAdmin();

        User::factory()->create([
            'name' => 'Needle Data Admin',
            'email' => 'needle.data-admin@example.test',
        ]);

        User::factory()->create([
            'name' => 'Unrelated Data Admin',
            'email' => 'unrelated.data-admin@example.test',
        ]);

        $component = Livewire::actingAs($admin)->test(Index::class);

        foreach (['products', 'users', 'import_batches', 'roles', 'permissions'] as $resource) {
            $component
                ->call('selectResource', $resource)
                ->assertSet('resource', $resource);
        }

        $component
            ->call('selectResource', 'users')
            ->set('search', 'needle.data-admin@example.test')
            ->assertSee('needle.data-admin@example.test')
            ->assertDontSee('unrelated.data-admin@example.test');
    }

    public function test_password_and_remember_token_are_not_exposed_in_user_list_or_detail(): void
    {
        $admin = $this->createSuperAdmin();
        $user = User::factory()->create([
            'name' => 'Sensitive Data User',
            'email' => 'sensitive.data-admin@example.test',
        ]);

        $passwordSentinel = 'DATA_ADMIN_PASSWORD_MUST_NOT_BE_EXPOSED';
        $rememberTokenSentinel = 'DATA_ADMIN_REMEMBER_TOKEN_MUST_NOT_BE_EXPOSED';

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => $passwordSentinel,
                'remember_token' => $rememberTokenSentinel,
            ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('selectResource', 'users')
            ->assertSee('sensitive.data-admin@example.test')
            ->assertDontSee($passwordSentinel, false, false)
            ->assertDontSee($rememberTokenSentinel, false, false)
            ->call('viewRecord', $user->id)
            ->assertSet('viewingId', $user->id)
            ->assertSee('sensitive.data-admin@example.test')
            ->assertDontSee($passwordSentinel, false, false)
            ->assertDontSee($rememberTokenSentinel, false, false);
    }

    public function test_invalid_resource_selection_falls_back_to_products(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertSet('resource', 'products')
            ->call('selectResource', 'sessions')
            ->assertSet('resource', 'products');
    }

    private function createSuperAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        return $admin;
    }
}
