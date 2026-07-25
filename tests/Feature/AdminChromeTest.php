<?php

namespace Tests\Feature;

use App\Livewire\ActivityLogs\Index as ActivityLogIndex;
use App\Livewire\Products\Index as ProductIndex;
use App\Models\AdminActivityLog;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_product_filters_use_custom_accessible_dropdowns(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('id="product-status"', false)
            ->assertSee('id="product-status-listbox"', false)
            ->assertSee('id="product-per-page"', false)
            ->assertSee('id="product-per-page-listbox"', false)
            ->assertSee('aria-haspopup="listbox"', false)
            ->assertSee('role="listbox"', false)
            ->assertSee('Tất cả trạng thái')
            ->assertSee('20 dòng / trang')
            ->assertDontSee('<select id="product-status"', false)
            ->assertDontSee('<select id="product-per-page"', false);
    }

    public function test_all_remaining_admin_dropdowns_use_the_shared_custom_component(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        AdminActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'auth.login',
            'description' => 'Đăng nhập kiểm tra dropdown.',
        ]);

        $this->actingAs($user);

        $this->get(route('admin.activity.index'))
            ->assertOk()
            ->assertSee('id="activity-action"', false)
            ->assertSee('id="activity-action-listbox"', false)
            ->assertSee('id="activity-per-page"', false)
            ->assertSee('id="activity-per-page-listbox"', false)
            ->assertSee('Tất cả hành động')
            ->assertSee('25 dòng')
            ->assertSee('form-input form-input-leading-icon', false)
            ->assertSee('bg-slate-50 text-left text-xs', false)
            ->assertDontSee('<select', false);

        $this->get(route('admin.products.index', ['action' => 'create']))
            ->assertOk()
            ->assertSee('id="warranty-status"', false)
            ->assertSee('id="warranty-status-listbox"', false)
            ->assertSee('Còn bảo hành')
            ->assertDontSee('<select', false);

        $this->get(route('admin.users.index', ['action' => 'create']))
            ->assertOk()
            ->assertSee('id="user-role"', false)
            ->assertSee('id="user-role-listbox"', false)
            ->assertSee('viewer')
            ->assertSee('fixed inset-0 z-[70] grid place-items-center overflow-y-auto', false)
            ->assertSee('w-full max-w-2xl rounded-3xl', false)
            ->assertDontSee('<select', false);
    }

    public function test_activity_filter_accepts_the_empty_action_in_one_state_update(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        AdminActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'auth.login',
            'description' => 'Đăng nhập kiểm tra cập nhật bộ lọc.',
        ]);
        AdminActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'product.created',
            'description' => 'Thêm sản phẩm kiểm tra cập nhật bộ lọc.',
        ]);

        $this->actingAs($user);

        Livewire::test(ActivityLogIndex::class)
            ->set('action', 'auth.login')
            ->assertSet('action', 'auth.login')
            ->assertSee('Đăng nhập kiểm tra cập nhật bộ lọc.')
            ->assertDontSee('Thêm sản phẩm kiểm tra cập nhật bộ lọc.')
            ->set('action', '')
            ->assertSet('action', '')
            ->assertSee('Tất cả hành động')
            ->assertSee('Đăng nhập kiểm tra cập nhật bộ lọc.')
            ->assertSee('Thêm sản phẩm kiểm tra cập nhật bộ lọc.');
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

    public function test_product_pdf_download_requires_confirmation(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        $product = Product::factory()->create([
            'name' => 'Sản phẩm tải tem xác nhận',
            'imei' => '012345678901234',
        ]);

        $this->actingAs($user);

        Livewire::test(ProductIndex::class)
            ->call('confirmDownload', $product->id)
            ->assertSet('showDownloadModal', true)
            ->assertSet('downloadProductId', $product->id)
            ->assertSee('Xác nhận tải tem PDF?')
            ->assertSee('Sản phẩm tải tem xác nhận')
            ->assertSee('Tải về máy')
            ->call('closeDownload')
            ->assertSet('showDownloadModal', false)
            ->assertSet('downloadProductId', null);
    }

    public function test_selected_products_replace_the_result_toolbar_actions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        Product::factory()->count(2)->create();

        $this->actingAs($user);

        Livewire::test(ProductIndex::class)
            ->assertSee('Kết quả:')
            ->assertSee('Chọn tất cả')
            ->call('selectCurrentPage')
            ->assertSee('Đã chọn 2 sản phẩm.')
            ->assertSee('Bỏ chọn')
            ->assertSee('Xuất tem PDF')
            ->assertDontSee('Kết quả:')
            ->assertDontSee('Chọn tất cả')
            ->call('clearSelection')
            ->assertSet('selected', []);
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
            ->assertSeeInOrder([
                '<th class="px-5 py-3">Người thao tác</th>',
                '<th class="px-5 py-3">Hành động</th>',
                '<th class="px-5 py-3">Chi tiết</th>',
                '<th class="px-5 py-3">IP</th>',
                '<th class="min-w-40 px-5 py-3">Thời gian</th>',
            ], false)
            ->assertSee('Thời gian')
            ->assertSee('18/07/2026 18:59')
            ->assertDontSee('18:59:00');
    }
}
