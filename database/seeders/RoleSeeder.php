<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $roles = [            
            'Super-Admin',
            'admin',           
            'country-manager',
            'regional-manager',
            'zone-manager',
            'division-manager',
            'district-manager',
            'sub-district-manager',
            'territory-manager',
            'area-officer',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'sanctum',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Roles
        |--------------------------------------------------------------------------
        */

        $admin = Role::findByName('Super-Admin', 'sanctum');
        $countryManager = Role::findByName('country-manager', 'sanctum');
        $regionalManager = Role::findByName('regional-manager', 'sanctum');
        $zoneManager = Role::findByName('zone-manager', 'sanctum');
        $divisionManager = Role::findByName('division-manager', 'sanctum');
        $districtManager = Role::findByName('district-manager', 'sanctum');
        $subDistrictManager = Role::findByName('sub-district-manager', 'sanctum');
        $territoryManager = Role::findByName('territory-manager', 'sanctum');
        $areaOfficer = Role::findByName('area-officer', 'sanctum');               

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        $admin->givePermissionTo(
            Permission::all()
        );

        /*
        |--------------------------------------------------------------------------
        | Country Manager
        |--------------------------------------------------------------------------
        */

        $countryManager->givePermissionTo([
            'user.view',
            'company.view',
            'company.create',
            'company.edit',

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
        ]);

        /*
        |--------------------------------------------------------------------------
        | Regional Manager
        |--------------------------------------------------------------------------
        */

        $regionalManager->givePermissionTo([
            'user.view',
            'company.view',
            'region.view',
            'region.edit',
            'zone.view',
            'zone.create',
            'zone.edit',
            'division.view',
            'division.create',
            'division.edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Zone Manager
        |--------------------------------------------------------------------------
        */

        $zoneManager->givePermissionTo([
            'user.view',

            'zone.view',
            'zone.edit',

            'division.view',
            'division.edit',

            'district.view',
            'district.create',
            'district.edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Division Manager
        |--------------------------------------------------------------------------
        */

        $divisionManager->givePermissionTo([
            'user.view',

            'division.view',

            'district.view',
            'district.edit',

            'sub-district.view',
            'sub-district.create',
            'sub-district.edit',

            'farm.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | District Manager
        |--------------------------------------------------------------------------
        */

        $districtManager->givePermissionTo([
            'user.view',

            'district.view',

            'sub-district.view',
            'sub-district.edit',

            'territory.view',
            'territory.create',
            'territory.edit',

            'farm.view',
            'farm.create',
            'farm.edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Sub District Manager
        |--------------------------------------------------------------------------
        */

        $subDistrictManager->givePermissionTo([
            'user.view',

            'sub-district.view',

            'territory.view',
            'territory.create',
            'territory.edit',

            'area.view',
            'area.create',
            'area.edit',

            'farm.view',
            'farm.create',
            'farm.edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Territory Manager
        |--------------------------------------------------------------------------
        */

        $territoryManager->givePermissionTo([
            'user.view',

            'territory.view',

            'area.view',
            'area.create',
            'area.edit',

            'farm.view',
            'farm.create',
            'farm.edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Area Officer
        |--------------------------------------------------------------------------
        */

        $areaOfficer->givePermissionTo([
            'user.view',

            'area.view',

            'farm.view',
            'farm.create',
            'farm.edit',

            'attendance.view',
            'location.view',
        ]);
         
    }
}

