<?php

namespace Tests\Feature;

use App\Models\AdminActivityLog;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_custom_administration_modules_and_recent_actions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        Product::factory()->create();
        AdminActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'product.created',
            'description' => 'Thêm sản phẩm kiểm thử.',
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('<title>Site administration | 24hStore Administration</title>', false)
            ->assertSee('admin-favicon.svg')
            ->assertSee('laravel-logo.svg')
            ->assertSee('Xin chào,')
            ->assertSee('Administration')
            ->assertSee('Bảo hành &amp; kho', false)
            ->assertSee('Xác thực &amp; phân quyền', false)
            ->assertSee('Hoạt động gần đây')
            ->assertSee('Thêm sản phẩm kiểm thử.');
    }
}
