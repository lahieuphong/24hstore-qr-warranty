<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'products.import',
            'products.print',
            'users.manage',
            'activity.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate('super-admin', 'web')->syncPermissions($permissions);
        Role::findOrCreate('warehouse-manager', 'web')->syncPermissions([
            'dashboard.view',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'products.import',
            'products.print',
        ]);
        Role::findOrCreate('warehouse-staff', 'web')->syncPermissions([
            'dashboard.view',
            'products.view',
            'products.create',
            'products.update',
            'products.import',
            'products.print',
        ]);
        Role::findOrCreate('warranty-staff', 'web')->syncPermissions([
            'dashboard.view',
            'products.view',
            'products.update',
            'products.print',
        ]);
        Role::findOrCreate('viewer', 'web')->syncPermissions([
            'dashboard.view',
            'products.view',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
