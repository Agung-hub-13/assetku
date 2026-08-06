<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    $superAdmin = Role::findByName('super-admin');
    $superAdmin->givePermissionTo(Permission::all());

    $branchAdmin = Role::findByName('branch-admin');

    // semua permission untuk asset
    $assetPermissions = Permission::where('name', 'like', 'asset.%')->pluck('name')->toArray();

    // semua permission untuk work_order
    $workOrderPermissions = Permission::where('name', 'like', 'work_order.%')->pluck('name')->toArray();

    $branchAdmin->givePermissionTo(array_merge(
        ['dashboard.view'],
        $assetPermissions,
        $workOrderPermissions
    ));
}

}
