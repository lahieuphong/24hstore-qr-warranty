<?php

namespace Tests\Feature;

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
            ->assertSee('Xem trang tra cứu')
            ->assertSee('Đổi mật khẩu')
            ->assertSee('Đăng xuất');
    }

    public function test_admin_favicon_asset_exists(): void
    {
        $this->assertFileExists(public_path('admin-favicon.svg'));
    }
}
