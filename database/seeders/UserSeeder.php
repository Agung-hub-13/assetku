<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdminUser = User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name'     => 'Super Administrator',
            'password' => Hash::make('password123'),
            'phone'    => '081234567890',
        ]);
        $superAdminUser->syncRoles(['Super Admin']);

        // Admin Asset
        $adminAssetUser = User::firstOrCreate([
            'email' => 'admin.asset@admin.com',
        ], [
            'name'     => 'Budi (Admin Aset)',
            'password' => Hash::make('password123'),
            'phone'    => '081234567891',
        ]);
        $adminAssetUser->syncRoles(['Admin Asset']);

        // Supervisor
        $supervisorUser = User::firstOrCreate([
            'email' => 'spv@admin.com',
        ], [
            'name'     => 'Siti (Supervisor)',
            'password' => Hash::make('password123'),
            'phone'    => '081234567892',
        ]);
        $supervisorUser->syncRoles(['Supervisor']);

        // Employee
        $employeeUser = User::firstOrCreate([
            'email' => 'employee@admin.com',
        ], [
            'name'     => 'Ahmad (Staff Employee)',
            'password' => Hash::make('password123'),
            'phone'    => '081234567893',
        ]);
        $employeeUser->syncRoles(['Staff Mobile']);
    }
}