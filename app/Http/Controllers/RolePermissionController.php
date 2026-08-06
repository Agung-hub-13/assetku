<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    /**
     * Tampilkan halaman Matrix Role & Permission.
     */
    public function index(Request $request)
    {
        $roles = Role::whereNotIn('name', ['Super Admin', 'super-admin'])->get();
        $selectedRoleId = $request->get('role_id', $roles->first()?->id);

        $selectedRole = $selectedRoleId ? Role::findById($selectedRoleId) : null;
        $permissions = Permission::all();

        // Mengelompokkan permission berdasarkan modul/fitur untuk tampilan Matrix
        $groupedPermissions = [
            'Akses Platform' => [
                'Platform' => $permissions->whereIn('name', ['access-desktop', 'access-mobile']),
            ],
            'Dashboard & Profil' => [
                'Utama' => $permissions->whereIn('name', ['dashboard.view', 'profile.manage']),
            ],
            'Manajemen Pengguna' => [
                'Users' => $permissions->filter(fn($p) => str_starts_with($p->name, 'user.')),
                'Roles' => $permissions->filter(fn($p) => str_starts_with($p->name, 'role.')),
                'Departemen' => $permissions->filter(fn($p) => str_starts_with($p->name, 'asset-departments.')),
            ],
            'Master Data Aset' => [
                'Daftar Aset' => $permissions->filter(fn($p) => str_starts_with($p->name, 'asset.')),
                'Kategori Aset' => $permissions->filter(fn($p) => str_starts_with($p->name, 'asset-categories.')),
                'Lokasi Aset' => $permissions->filter(fn($p) => str_starts_with($p->name, 'asset-locations.')),
            ],
            'Operasional Aset' => [
                'Mutasi (Transfer)' => $permissions->filter(fn($p) => str_starts_with($p->name, 'transfer.')),
                'Peminjaman (Loan)' => $permissions->filter(fn($p) => str_starts_with($p->name, 'loan.')),
                'Pemeliharaan (Maintenance)' => $permissions->filter(fn($p) => str_starts_with($p->name, 'maintenance.')),
                'Stock Opname (Audit)' => $permissions->filter(fn($p) => str_starts_with($p->name, 'audit.')),
            ],
            // DITAMBAHKAN: Kelompok Log & Laporan
            'Log & Laporan' => [
                'Log Aktivitas' => $permissions->filter(fn($p) => str_starts_with($p->name, 'logs.')),
                'Laporan Asset' => $permissions->filter(fn($p) => str_starts_with($p->name, 'reports.')),
            ],
            'Integrasi & Notifikasi' => [
                'Sistem' => $permissions->whereIn('name', ['notification.view', 'accurate.manage']),
            ],
        ];

        $rolePermissions = $selectedRole ? $selectedRole->permissions->pluck('name')->toArray() : [];

        return view('admin.roles.permissions', compact('roles', 'selectedRole', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update permission yang di-assign ke role terpilih.
     */
    public function update(Request $request)
    {
        $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'permissions' => ['array'],
        ]);

        $role = Role::findById($request->role_id);

        if (in_array($role->name, ['Super Admin', 'super-admin'])) {
            return back()->with('error', 'Hak akses Super Admin tidak dapat diubah.');
        }

        // Sync permission baru (otomatis hapus yang di-uncheck)
        $role->syncPermissions($request->permissions ?? []);

        // RESET CACHE SPATIE OTOMATIS
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', "Permission untuk role '{$role->name}' berhasil diperbarui.");
    }
}
