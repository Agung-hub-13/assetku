<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin (Dapat Semua Permission)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Admin Asset
        $admin = Role::firstOrCreate(['name' => 'Admin Asset', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'access-desktop', 'dashboard.view', 'profile.manage', 'user.view', 
            'asset-departments.view',
            'asset.view', 'asset.create', 'asset.edit',
            'asset-categories.view', 'asset-categories.create', 'asset-categories.edit',
            'asset-locations.view', 'asset-locations.create', 'asset-locations.edit',
            'transfer.view', 'transfer.create', 'transfer.edit', 'transfer.approve',
            'loan.view', 'loan.create', 'loan.edit',
            'maintenance.view', 'maintenance.create', 'maintenance.edit',
            'audit.view', 'audit.create', 'audit.edit',
            'notification.view',
        ]);

        // 3. Supervisor
        $supervisor = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        $supervisor->syncPermissions([
            'access-desktop', 'dashboard.view', 'profile.manage',
            'asset.view', 'transfer.view', 'transfer.approve', 'loan.view', 'loan.approve',
            'notification.view',
        ]);

        // 4. Staff Mobile
        $staffMobile = Role::firstOrCreate(['name' => 'Staff Mobile', 'guard_name' => 'web']);
        $staffMobile->syncPermissions([
            'access-mobile', 'dashboard.view', 'profile.manage',
            'asset.view', 'asset-locations.view',
            'transfer.view', 'transfer.create',
            'loan.view', 'loan.create',
            'maintenance.view', 'maintenance.create',
            'notification.view',
        ]);
    }
}