<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = (string) config('admin.name', 'Quản trị hệ thống');
        $email = Str::lower(trim((string) config('admin.email', 'admin@24hstore.local')));
        $password = (string) config('admin.password', 'ChangeMeNow!2026');

        if (app()->isProduction() && (
            $password === 'ChangeMeNow!2026'
            || Str::contains($password, ['replace_with', 'changeme'])
            || mb_strlen($password) < 12
        )) {
            throw new RuntimeException('Hãy đặt ADMIN_PASSWORD riêng, dài ít nhất 12 ký tự trước khi seed production.');
        }

        $admin = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );

        if (! $admin->wasRecentlyCreated) {
            $admin->update([
                'name' => $name ?: $admin->name,
                'is_active' => true,
            ]);
        }

        $admin->syncRoles(['super-admin']);
    }
}
