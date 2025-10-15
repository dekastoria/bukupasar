<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'markets.view',
            'markets.create',
            'markets.update',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'tenants.view',
            'tenants.create',
            'tenants.update',
            'tenants.delete',
            'transactions.view',
            'transactions.create',
            'transactions.update',
            'transactions.delete',
            'transactions.edit-own',
            'transactions.delete-own',
            'payments.view',
            'payments.create',
            'payments.update',
            'reports.view',
            'reports.export',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'admin_pusat' => $permissions,
            'admin_pasar' => [
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                'categories.view',
                'categories.create',
                'categories.update',
                'categories.delete',
                'tenants.view',
                'tenants.create',
                'tenants.update',
                'tenants.delete',
                'transactions.view',
                'transactions.create',
                'transactions.update',
                'transactions.delete',
                'transactions.edit-own',
                'transactions.delete-own',
                'payments.view',
                'payments.create',
                'payments.update',
                'reports.view',
                'reports.export',
                'settings.manage',
            ],
            'inputer' => [
                'categories.view',
                'tenants.view',
                'transactions.view',
                'transactions.create',
                'transactions.edit-own',
                'transactions.delete-own',
                'payments.view',
                'payments.create',
                'reports.view',
            ],
            'viewer' => [
                'categories.view',
                'tenants.view',
                'transactions.view',
                'payments.view',
                'reports.view',
            ],
        ];

        foreach ($roles as $roleName => $assignedPermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($assignedPermissions);
        }
    }
}
