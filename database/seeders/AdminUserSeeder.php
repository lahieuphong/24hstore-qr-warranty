<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = (string) config('admin.name', 'Quản trị hệ thống');
        $email = Str::lower(trim((string) config('admin.email', 'admin@gmail.com')));
        $password = (string) config('admin.password', 'Aa123456');

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('ADMIN_EMAIL phải là một địa chỉ email hợp lệ.');
        }

        if ($password === '') {
            throw new RuntimeException('ADMIN_PASSWORD không được để trống.');
        }

        if (app()->isProduction() && $email === 'admin@gmail.com') {
            throw new RuntimeException('Hãy đặt ADMIN_EMAIL riêng trước khi seed production.');
        }

        if (app()->isProduction() && (
            $password === 'Aa123456'
            || Str::contains($password, ['replace_with', 'changeme'])
            || mb_strlen($password) < 12
        )) {
            throw new RuntimeException('Hãy đặt ADMIN_PASSWORD riêng, dài ít nhất 12 ký tự trước khi seed production.');
        }

        DB::transaction(function () use ($email, $name, $password): void {
            $admin = User::query()
                ->where('is_environment_admin', true)
                ->lockForUpdate()
                ->first();

            if (! $admin) {
                $emailOwner = User::query()
                    ->where('email', $email)
                    ->lockForUpdate()
                    ->first();

                if ($emailOwner && ! $emailOwner->hasRole('super-admin')) {
                    throw new RuntimeException('ADMIN_EMAIL đang được sử dụng bởi một tài khoản khác.');
                }

                if ($emailOwner) {
                    $admin = $emailOwner;
                } else {
                    $superAdmins = User::query()
                        ->whereHas('roles', fn ($query) => $query
                            ->where('name', 'super-admin')
                            ->where('guard_name', 'web'))
                        ->oldest('id')
                        ->limit(2)
                        ->lockForUpdate()
                        ->get();

                    if ($superAdmins->count() === 1) {
                        $admin = $superAdmins->first();
                    } elseif ($superAdmins->count() > 1) {
                        throw new RuntimeException(
                            'Không thể xác định tài khoản quản trị môi trường. '
                            .'Hãy deploy một lần với ADMIN_EMAIL của super-admin hiện tại trước khi đổi email.',
                        );
                    }
                }
            }

            if ($admin && $admin->email !== $email) {
                $emailBelongsToAnotherUser = User::query()
                    ->where('email', $email)
                    ->where('id', '!=', $admin->getKey())
                    ->exists();

                if ($emailBelongsToAnotherUser) {
                    throw new RuntimeException('ADMIN_EMAIL đang được sử dụng bởi một tài khoản khác.');
                }
            }

            $admin ??= new User;
            $hadStoredCredentials = $admin->exists;
            $emailChanged = $hadStoredCredentials && $admin->email !== $email;
            $passwordChanged = ! $hadStoredCredentials || ! Hash::check($password, (string) $admin->password);
            $credentialsChanged = $emailChanged || ($hadStoredCredentials && $passwordChanged);

            $admin->forceFill([
                'name' => $name ?: ($admin->name ?: 'Quản trị hệ thống'),
                'email' => $email,
                'email_verified_at' => $admin->email_verified_at ?? now(),
                'is_active' => true,
                'is_environment_admin' => true,
            ]);

            if ($passwordChanged) {
                $admin->password = $password;
            }

            if ($credentialsChanged) {
                $admin->setRememberToken(null);
            }

            $admin->save();
            $admin->syncRoles(['super-admin']);

            if ($credentialsChanged) {
                DB::table('sessions')->where('user_id', $admin->getKey())->delete();
            }
        });
    }
}
