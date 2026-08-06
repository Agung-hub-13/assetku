<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permission Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. DAFTAR PERMISSION LENGKAP SESUAI ROUTE
        $permissions = [
            // Platform & Core Access
            'access-desktop',
            'access-mobile',
            'dashboard.view',
            'profile.manage',

            // Management User & Roles
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',

            // Master Data (Categories, Locations, Departments)
            'asset-categories.view', 'asset-categories.create', 'asset-categories.edit', 'asset-categories.delete',
            'asset-locations.view', 'asset-locations.create', 'asset-locations.edit', 'asset-locations.delete',
            'asset-departments.view', 'asset-departments.create', 'asset-departments.edit', 'asset-departments.delete',

            // Master Aset & Attachments
            'asset.view', 'asset.create', 'asset.edit', 'asset.delete',

            // Mutasi Aset (Transfer)
            'transfer.view', 'transfer.create', 'transfer.edit', 'transfer.delete',
            'transfer.approve', 'transfer.reject',

            // Peminjaman Aset (Loan)
            'loan.view', 'loan.create', 'loan.edit', 'loan.delete',
            'loan.approve', 'loan.reject', 'loan.handover', 'loan.return',

            // Pemeliharaan Aset (Maintenance)
            'maintenance.view', 'maintenance.create', 'maintenance.edit', 'maintenance.delete',
            'maintenance.progress',

            // Audit Aset (Stock Opname)
            'audit.view', 'audit.create', 'audit.edit', 'audit.delete', 'audit.scan',

            // Logs, Reports & Notifications
            'logs.view', 'logs.delete',
            'reports.view', 'reports.export',
            'notification.view',

            // Integrasi Accurate
            'accurate.manage',
        ];

        // Buat semua permission ke Guard 'web'
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2. DEFAULT ROLES & PERMISSION ASSIGNMENT

        // A. Super Admin (Akses Semuanya)
        $superAdmin = Role::findOrCreate('Super Admin', 'web');
        $superAdmin->syncPermissions(Permission::all());

        // B. Admin Asset (Akses Pengelolaan Master & Operasional Aset)
        $adminAsset = Role::findOrCreate('Admin Asset', 'web');
        $adminAsset->syncPermissions([
            'access-desktop',
            'access-mobile',
            'dashboard.view',
            'profile.manage',
            'asset-categories.view', 'asset-categories.create', 'asset-categories.edit', 'asset-categories.delete',
            'asset-locations.view', 'asset-locations.create', 'asset-locations.edit', 'asset-locations.delete',
            'asset-departments.view', 'asset-departments.create', 'asset-departments.edit', 'asset-departments.delete',
            'asset.view', 'asset.create', 'asset.edit', 'asset.delete',
            'transfer.view', 'transfer.create', 'transfer.edit', 'transfer.delete', 'transfer.approve', 'transfer.reject',
            'loan.view', 'loan.create', 'loan.edit', 'loan.delete', 'loan.approve', 'loan.reject', 'loan.handover', 'loan.return',
            'maintenance.view', 'maintenance.create', 'maintenance.edit', 'maintenance.delete', 'maintenance.progress',
            'audit.view', 'audit.create', 'audit.edit', 'audit.delete', 'audit.scan',
            'logs.view',
            'reports.view', 'reports.export',
            'notification.view',
        ]);

        // C. Supervisor / Manager (Approver & Monitoring Laporan)
        $supervisor = Role::findOrCreate('Supervisor', 'web');
        $supervisor->syncPermissions([
            'access-desktop',
            'access-mobile',
            'dashboard.view',
            'profile.manage',
            'asset.view',
            'transfer.view', 'transfer.approve', 'transfer.reject',
            'loan.view', 'loan.approve', 'loan.reject',
            'maintenance.view',
            'audit.view',
            'reports.view', 'reports.export',
            'logs.view',
            'notification.view',
        ]);

        // D. Karyawan / User Biasa (Akses Mobile & Pengajuan Pinjam/Mutasi)
        $userRole = Role::findOrCreate('User', 'web');
        $userRole->syncPermissions([
            'access-desktop',
            'access-mobile',
            'dashboard.view',
            'profile.manage',
            'asset.view',
            'transfer.view', 'transfer.create',
            'loan.view', 'loan.create',
            'notification.view',
        ]);
    }
}