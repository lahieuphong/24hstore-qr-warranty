<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
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
            ->assertSee('placeholder="Nhập email..."', false)
            ->assertSee('placeholder="Nhập mật khẩu..."', false)
            ->assertSee('action="'.route('login.store').'"', false)
            ->assertSee('data-login-form', false)
            ->assertSee('data-csrf-url="'.route('login.csrf').'"', false)
            ->assertSee('data-login-submit', false)
            ->assertSee('data-login-status', false)
            ->assertSee('Đang xác thực...')
            ->assertSee('class="form-input form-input-trailing-icon login-password-input"', false)
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

        $response = $this->post('/admin/login/?next=/admin/products%3Fstatus%3Dactive', [
            'email' => $user->email,
            'password' => 'password',
            'next' => '/admin/products?status=active',
        ]);
        $response
            ->assertStatus(303)
            ->assertRedirect(route('login.success'));

        $this->assertAuthenticatedAs($user);

        $this->get(route('login.success'))
            ->assertOk()
            ->assertSee('<title>Đăng nhập thành công | 24hStore Administration</title>', false)
            ->assertSee('Đăng nhập thành công')
            ->assertDontSee('<meta http-equiv="refresh"', false)
            ->assertSee('data-continue-url="'.route('login.success.complete').'"', false)
            ->assertDontSee('/admin/products?status=active', false);

        $this->get(route('login.success.complete'))
            ->assertRedirect(url('/admin/products?status=active'));
        $this->get(route('login.success.complete'))
            ->assertRedirect(url('/admin').'/');
    }

    #[DataProvider('unsafeNextUrls')]
    public function test_admin_login_rejects_an_unsafe_next_url(string $next): void
    {
        $user = User::factory()->create();

        $response = $this->post('/admin/login/', [
            'email' => $user->email,
            'password' => 'password',
            'next' => $next,
        ]);

        $response
            ->assertStatus(303)
            ->assertRedirect(route('login.success'));

        $this->get(route('login.success.complete'))
            ->assertRedirect(url('/admin').'/');
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

    public function test_login_form_fetches_the_current_csrf_token_without_rotating_other_forms(): void
    {
        $response = $this
            ->withSession(['_token' => 'current-token'])
            ->get(route('login.csrf'));

        $response
            ->assertOk()
            ->assertJsonStructure(['token'])
            ->assertHeader('Pragma', 'no-cache');
        $this->assertStringContainsString(
            'no-store',
            (string) $response->headers->get('Cache-Control'),
        );
        $this->assertSame('current-token', $response->json('token'));
    }

    public function test_expired_login_csrf_returns_to_a_fresh_form_instead_of_the_419_page(): void
    {
        $originalEnvironment = $this->app->environment();

        try {
            $this->app['env'] = 'production';
            $response = $this
                ->withSession(['_token' => 'current-token'])
                ->post(route('login.store'), [
                    '_token' => 'stale-token',
                    'email' => 'admin@example.com',
                    'password' => 'Secret123!',
                    'next' => '/admin/',
                ]);
        } finally {
            $this->app['env'] = $originalEnvironment;
        }

        $response
            ->assertStatus(303)
            ->assertRedirect(AuthenticatedSessionController::loginUrl('/admin/', ['expired' => 1]));

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Phiên đăng nhập đã hết hạn.')
            ->assertSee('Trang đã được làm mới. Vui lòng đăng nhập lại.');
    }

    public function test_guest_cannot_open_login_transition_routes_directly(): void
    {
        foreach (['login.success', 'login.success.complete'] as $routeName) {
            $this->get(route($routeName))
                ->assertRedirect(AuthenticatedSessionController::loginUrl());
        }
    }

    public function test_environment_credentials_rotate_the_same_admin_account_and_invalidate_the_old_login(): void
    {
        config()->set([
            'admin.email' => 'old-admin@example.com',
            'admin.password' => 'OldPassword123!',
            'admin.name' => 'Quản trị từ môi trường',
        ]);

        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'old-admin@example.com')->sole();
        $adminId = $admin->getKey();
        $this->assertTrue($admin->is_environment_admin);
        $this->assertTrue($admin->hasRole('super-admin'));

        $admin->forceFill([
            'remember_token' => 'old-remember-token',
            'is_active' => false,
        ])->save();
        $admin->syncRoles(['viewer']);
        DB::table('sessions')->insert([
            'id' => 'old-admin-session',
            'user_id' => $adminId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);
        $this->assertTrue(Hash::check('OldPassword123!', $admin->password));

        config()->set([
            'admin.email' => 'new-admin@example.com',
            'admin.password' => 'NewPassword456!',
        ]);

        $this->seed(DatabaseSeeder::class);

        $rotated = User::query()->where('email', 'new-admin@example.com')->sole();
        $this->assertSame($adminId, $rotated->getKey());
        $this->assertTrue($rotated->is_environment_admin);
        $this->assertTrue($rotated->is_active);
        $this->assertSame(['super-admin'], $rotated->roles->pluck('name')->all());
        $this->assertTrue(Hash::check('NewPassword456!', $rotated->password));
        $this->assertFalse(Hash::check('OldPassword123!', $rotated->password));
        $this->assertNull($rotated->remember_token);
        $this->assertDatabaseMissing('sessions', [
            'id' => 'old-admin-session',
        ]);

        $rotatedHash = $rotated->password;
        $this->seed(DatabaseSeeder::class);
        $rotated->refresh();
        $this->assertSame($rotatedHash, $rotated->password);
        $this->assertDatabaseMissing('users', [
            'email' => 'old-admin@example.com',
        ]);
        $this->assertCount(1, User::all());

        $this->post('/admin/login/', [
            'email' => 'old-admin@example.com',
            'password' => 'OldPassword123!',
            'next' => '/admin/',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login/', [
            'email' => 'new-admin@example.com',
            'password' => 'NewPassword456!',
            'next' => '/admin/',
        ])->assertStatus(303)
            ->assertRedirect(route('login.success'));
        $this->assertAuthenticatedAs($rotated);
    }

    public function test_production_accepts_a_non_default_twelve_character_admin_password(): void
    {
        $originalEnvironment = $this->app->environment();
        config()->set([
            'admin.email' => 'production-admin@example.com',
            'admin.password' => 'ProdPass123!',
        ]);

        try {
            $this->app['env'] = 'production';
            $this->artisan('db:seed', [
                '--class' => DatabaseSeeder::class,
                '--force' => true,
            ])->assertSuccessful();
        } finally {
            $this->app['env'] = $originalEnvironment;
        }

        $admin = User::query()->where('email', 'production-admin@example.com')->sole();
        $this->assertTrue($admin->is_environment_admin);
        $this->assertTrue($admin->is_active);
        $this->assertTrue($admin->hasRole('super-admin'));
        $this->assertTrue(Hash::check('ProdPass123!', $admin->password));
    }

    public function test_production_rejects_the_default_admin_email(): void
    {
        $originalEnvironment = $this->app->environment();
        config()->set([
            'admin.email' => 'admin@gmail.com',
            'admin.password' => 'ProductionPassword123!',
        ]);

        try {
            $this->app['env'] = 'production';
            $this->artisan('db:seed', [
                '--class' => DatabaseSeeder::class,
                '--force' => true,
            ])->assertSuccessful();
            $this->fail('Seeder phải yêu cầu ADMIN_EMAIL riêng trên production.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Hãy đặt ADMIN_EMAIL riêng trước khi seed production.',
                $exception->getMessage(),
            );
        } finally {
            $this->app['env'] = $originalEnvironment;
        }
    }

    public function test_first_environment_admin_sync_rejects_an_existing_non_admin_email(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $legacyAdmin = User::factory()->create([
            'email' => 'legacy-admin@example.com',
        ]);
        $legacyAdmin->syncRoles(['super-admin']);
        User::factory()->create([
            'email' => 'existing-user@example.com',
        ]);

        config()->set([
            'admin.email' => 'existing-user@example.com',
            'admin.password' => 'ReplacementPassword456!',
        ]);

        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('Seeder không được chiếm tài khoản thường làm admin môi trường.');
        } catch (RuntimeException $exception) {
            $this->assertSame('ADMIN_EMAIL đang được sử dụng bởi một tài khoản khác.', $exception->getMessage());
        }

        $legacyAdmin->refresh();
        $this->assertTrue($legacyAdmin->hasRole('super-admin'));
        $this->assertNull($legacyAdmin->is_environment_admin);
    }

    public function test_first_environment_admin_sync_stops_when_multiple_super_admins_are_ambiguous(): void
    {
        $this->seed(RolePermissionSeeder::class);

        foreach (['first-admin@example.com', 'second-admin@example.com'] as $email) {
            $admin = User::factory()->create(['email' => $email]);
            $admin->syncRoles(['super-admin']);
        }

        config()->set([
            'admin.email' => 'new-admin@example.com',
            'admin.password' => 'ReplacementPassword456!',
        ]);

        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('Seeder phải dừng khi không thể xác định admin môi trường cũ.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'Không thể xác định tài khoản quản trị môi trường.',
                $exception->getMessage(),
            );
        }

        $this->assertDatabaseMissing('users', [
            'email' => 'new-admin@example.com',
        ]);
        $this->assertSame(0, User::query()->where('is_environment_admin', true)->count());
    }

    public function test_environment_admin_rotation_rejects_an_email_owned_by_another_user(): void
    {
        config()->set([
            'admin.email' => 'managed-admin@example.com',
            'admin.password' => 'ManagedPassword123!',
        ]);
        $this->seed(DatabaseSeeder::class);

        $environmentAdmin = User::query()->where('email', 'managed-admin@example.com')->sole();
        User::factory()->create([
            'email' => 'existing-user@example.com',
        ]);

        config()->set([
            'admin.email' => 'existing-user@example.com',
            'admin.password' => 'ReplacementPassword456!',
        ]);

        try {
            $this->artisan('db:seed', [
                '--class' => DatabaseSeeder::class,
                '--force' => true,
            ])->assertSuccessful();
            $this->fail('Seeder phải từ chối ADMIN_EMAIL đang thuộc về tài khoản khác.');
        } catch (RuntimeException $exception) {
            $this->assertSame('ADMIN_EMAIL đang được sử dụng bởi một tài khoản khác.', $exception->getMessage());
        }

        $environmentAdmin->refresh();
        $this->assertSame('managed-admin@example.com', $environmentAdmin->email);
        $this->assertTrue($environmentAdmin->is_environment_admin);
        $this->assertTrue(Hash::check('ManagedPassword123!', $environmentAdmin->password));
        $this->assertDatabaseHas('users', [
            'email' => 'existing-user@example.com',
        ]);
    }

    public function test_local_start_commands_sync_environment_credentials_before_serving(): void
    {
        foreach ([
            'start-app' => file_get_contents(base_path('start-app')),
            'start-app.bat' => file_get_contents(base_path('start-app.bat')),
        ] as $name => $script) {
            $migratePosition = strpos($script, 'artisan migrate');
            $seedPosition = strpos($script, 'artisan db:seed');
            $servePosition = strpos($script, 'artisan serve');

            $this->assertNotFalse($migratePosition, "{$name} phải chạy migration.");
            $this->assertNotFalse($seedPosition, "{$name} phải đồng bộ tài khoản quản trị.");
            $this->assertNotFalse($servePosition, "{$name} phải chạy server.");
            $this->assertLessThan($servePosition, $migratePosition);
            $this->assertLessThan($servePosition, $seedPosition);
        }

        $composer = json_decode(
            file_get_contents(base_path('composer.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $this->assertContains(
            '@php artisan migrate --seed --force --env=development',
            $composer['scripts']['dev'],
        );
    }

    public function test_production_entrypoint_always_syncs_environment_credentials_before_starting(): void
    {
        $script = file_get_contents(base_path('docker/entrypoint.sh'));
        $configPosition = strpos($script, 'artisan config:clear');
        $migratePosition = strpos($script, 'artisan migrate');
        $seedPosition = strpos($script, 'artisan db:seed --force');
        $optimizePosition = strpos($script, 'artisan optimize');
        $execPosition = strpos($script, 'exec "$@"');

        $this->assertNotFalse($configPosition);
        $this->assertSame(1, substr_count($script, 'artisan config:clear'));
        $this->assertNotFalse($migratePosition);
        $this->assertNotFalse($seedPosition);
        $this->assertNotFalse($optimizePosition);
        $this->assertNotFalse($execPosition);
        $this->assertLessThan($migratePosition, $configPosition);
        $this->assertLessThan($seedPosition, $migratePosition);
        $this->assertLessThan($optimizePosition, $seedPosition);
        $this->assertLessThan($execPosition, $optimizePosition);
        $this->assertStringNotContainsString('RUN_DATABASE_SEEDER', $script);
    }

    public function test_authenticated_user_cannot_return_to_the_admin_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/login/?next=/admin/')
            ->assertHeader('Location', url('/admin').'/');
    }
}
