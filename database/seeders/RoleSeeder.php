<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'super-admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        // super-admin gets all permissions
        $admin->givePermissionTo(Permission::all());

        // manager gets limited permissions
        $manager->givePermissionTo([
            'user.view',
            'company.view',
            'company.create',
            'company.edit',
        ]);

        // staff only view
        $staff->givePermissionTo([
            'company.view'
        ]);
    }
}
