<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private function getModules()
    {
        return [
            'Akses Perangkat / Fitur' => [
                'access-desktop' => 'Akses Versi Desktop / Web Admin',
                'access-mobile'  => 'Akses Versi Mobile App',
            ],
            'Manajemen Aset' => [
                'asset.view'   => 'Lihat Aset',
                'asset.create' => 'Tambah Aset',
                'asset.edit'   => 'Edit Aset',
                'asset.delete' => 'Hapus Aset',
            ],
            'Master Data' => [
                'asset-departments.view' => 'Lihat Departemen',
                'asset-categories.view'  => 'Lihat Kategori',
                'asset-locations.view'   => 'Lihat Lokasi',
            ],
            'Mutasi Aset' => [
                'transfer.view'    => 'Lihat Mutasi',
                'transfer.create'  => 'Buat Mutasi',
                'transfer.edit'    => 'Edit Mutasi',
                'transfer.delete'  => 'Hapus Mutasi',
                'transfer.approve' => 'Approve Mutasi',
            ],
            // TAMBAHKAN KELOMPOK LOG & LAPORAN DI SINI
            'Log & Laporan' => [
                'logs.view'      => 'Lihat Log Aktivitas',
                'logs.delete'    => 'Hapus Log Aktivitas',
                'reports.view'   => 'Lihat Laporan Asset',
                'reports.export' => 'Export Laporan Asset',
            ],
            'Pengaturan Akses' => [
                'user.view'   => 'Lihat User',
                'user.create' => 'Tambah User',
                'user.edit'   => 'Edit User',
                'user.delete' => 'Hapus User',
                'role.view'   => 'Lihat Role',
                'role.create' => 'Buat Role',
                'role.edit'   => 'Edit Role',
            ]
        ];
    }

    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $modules = $this->getModules();
        return view('admin.roles.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|unique:roles,name',
            'permissions' => 'nullable|array'
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil dibuat');
    }

    public function edit(Role $role)
    {
        $modules = $this->getModules();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('admin.roles.edit', compact('role', 'modules', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array'
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil diupdate');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Role Super Admin tidak dapat dihapus.');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil dihapus.');
    }
}
