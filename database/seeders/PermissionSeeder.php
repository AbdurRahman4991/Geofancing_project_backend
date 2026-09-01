<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            'company.view',
            'company.create',
            'company.edit',
            'company.delete',

            'country.view',
            'country.create',
            'country.edit',
            'country.delete',

            'region.view',
            'region.create',
            'region.edit',
            'region.delete',

            'zone.view',
            'zone.create',
            'zone.edit',
            'zone.delete',

            'division.view',
            'division.create',
            'division.edit',
            'division.delete',

            'district.view',
            'district.create',
            'district.edit',
            'district.delete',

            'sub-district.view',
            'sub-district.create',
            'sub-district.edit',
            'sub-district.delete',

            // অন্যান্য permissions
            'territory.view',
            'territory.create',
            'territory.edit',
            'territory.delete',

            'area.view',
            'area.create',
            'area.edit',
            'area.delete',

            'farm.view',
            'farm.create',
            'farm.edit',
            'farm.delete',

            'attendance.view',
            'attendance.create',
            'attendance.edit',
            'attendance.delete',

            'location.view',
            'location.create',
            'location.edit',
            'location.delete',


        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
