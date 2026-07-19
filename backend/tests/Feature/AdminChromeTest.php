<?php

namespace Tests\Feature;

use App\Models\AdminActivityLog;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChromeTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_uses_the_shared_django_admin_chrome(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('<title>Sản phẩm &amp; QR | 24hStore Administration</title>', false)
            ->assertSee('24hStore Administration')
            ->assertSee('Bảo hành &amp; kho', false)
            ->assertSee('Sản phẩm &amp; QR', false)
            ->assertSee('laravel-logo.svg')
            ->assertSee('Xem trang tra cứu')
            ->assertSee('Đổi mật khẩu')
            ->assertSee('Đăng xuất');
    }

    public function test_admin_favicon_asset_exists(): void
    {
        $this->assertFileExists(public_path('admin-favicon.svg'));
        $this->assertFileExists(public_path('laravel-logo.svg'));
    }

    public function test_product_table_shows_the_created_time_column(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        Product::factory()->create([
            'name' => 'Sản phẩm kiểm tra thời gian',
            'created_at' => '2026-07-18 18:59:00',
        ]);

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Thời gian')
            ->assertSee('18/07/2026 18:59');
    }

    public function test_user_table_uses_the_same_created_time_format(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        User::factory()->create([
            'name' => 'Người dùng kiểm tra thời gian',
            'created_at' => '2026-07-18 18:59:00',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Thời gian')
            ->assertDontSee('Ngày tạo')
            ->assertSee('18/07/2026 18:59');
    }

    public function test_activity_table_uses_the_same_created_time_format(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $log = AdminActivityLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'product.created',
            'description' => 'Hoạt động kiểm tra thời gian.',
        ]);
        $log->created_at = '2026-07-18 18:59:00';
        $log->saveQuietly();

        $this->actingAs($admin)
            ->get(route('admin.activity.index'))
            ->assertOk()
            ->assertSee('Thời gian')
            ->assertSee('18/07/2026 18:59')
            ->assertDontSee('18:59:00');
    }
}
