<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_the_django_style_admin_login_url(): void
    {
        $this->get('/admin/')
            ->assertHeader('Location', url('/admin/login').'/?next=/admin/');
    }

    public function test_admin_login_page_keeps_the_requested_next_path(): void
    {
        $this->get('/admin/login/?next=/admin/products')
            ->assertOk()
            ->assertSee('<title>Đăng nhập | 24hStore Administration</title>', false)
            ->assertSee('admin-favicon.svg')
            ->assertSee('laravel-logo.svg')
            ->assertSee('class="text-2xl font-black text-rose-950">24hStore QR Warranty</h1>', false)
            ->assertSee('Đăng nhập khu vực quản trị nội bộ')
            ->assertSee('placeholder="********"', false)
            ->assertSee('class="form-input login-password-input pr-12"', false)
            ->assertSee('class="login-checkbox"', false)
            ->assertSee('data-password-toggle', false)
            ->assertSee('aria-controls="password"', false)
            ->assertSee('aria-label="Hiện mật khẩu"', false)
            ->assertSee('name="next" value="/admin/products"', false);
    }

    public function test_guest_deep_link_is_preserved_in_the_login_url(): void
    {
        $this->get('/admin/products?status=active')
            ->assertHeader('Location', url('/admin/login').'/?next=/admin/products%3Fstatus%3Dactive');
    }

    public function test_admin_login_redirects_back_to_the_requested_admin_page(): void
    {
        $user = User::factory()->create();

        $this->post('/admin/login/?next=/admin/', [
            'email' => $user->email,
            'password' => 'password',
            'next' => '/admin/',
        ])->assertHeader('Location', url('/admin').'/');

        $this->assertAuthenticatedAs($user);
    }

    #[DataProvider('unsafeNextUrls')]
    public function test_admin_login_rejects_an_unsafe_next_url(string $next): void
    {
        $user = User::factory()->create();

        $this->post('/admin/login/', [
            'email' => $user->email,
            'password' => 'password',
            'next' => $next,
        ])->assertHeader('Location', url('/admin').'/');
    }

    /** @return array<string, array{string}> */
    public static function unsafeNextUrls(): array
    {
        return [
            'external URL' => ['https://example.com'],
            'scheme-relative URL' => ['//example.com'],
            'outside admin' => ['/outside'],
            'dot segments' => ['/admin/../../outside'],
            'encoded dot segments' => ['/admin/%2e%2e/outside'],
            'encoded backslash' => ['/admin/%5cexample'],
        ];
    }

    public function test_legacy_login_url_redirects_to_the_admin_login_url(): void
    {
        $this->get('/login')
            ->assertHeader('Location', url('/admin/login').'/?next=/admin/');
    }

    public function test_authenticated_user_cannot_return_to_the_admin_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/login/?next=/admin/')
            ->assertHeader('Location', url('/admin').'/');
    }
}
