<?php

// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;

// class RoleSeeder extends Seeder
// {
//     public function run(): void
//     {
//         $admin = Role::firstOrCreate(['name' => 'super-admin']);
//         $manager = Role::firstOrCreate(['name' => 'manager']);
//         $staff = Role::firstOrCreate(['name' => 'staff']);

//         // super-admin gets all permissions
//         $admin->givePermissionTo(Permission::all());

//         // manager gets limited permissions
//         $manager->givePermissionTo([
//             'user.view',
//             'company.view',
//             'company.create',
//             'company.edit',
//         ]);

//         // staff only view
//         $staff->givePermissionTo([
//             'company.view'
//         ]);
//     }


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

            // System Level
            'super-admin',

            // Country / Region / Zone Management
            'country-manager',
            'regional-manager',
            'zone-manager',

            // Geographic Management
            'division-manager',
            'district-manager',
            'sub-district-manager',

            // Field Management
            'territory-manager',
            'area-officer',
            //'farm-officer',
            'field-employee',

            // General Roles
            // 'manager',
            // 'staff',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Roles
        |--------------------------------------------------------------------------
        */

        $admin = Role::findByName('super-admin');

        $countryManager = Role::findByName('country-manager');
        $regionalManager = Role::findByName('regional-manager');
        $zoneManager = Role::findByName('zone-manager');

        $divisionManager = Role::findByName('division-manager');
        $districtManager = Role::findByName('district-manager');
        $subDistrictManager = Role::findByName('sub-district-manager');

        $territoryManager = Role::findByName('territory-manager');
        $areaOfficer = Role::findByName('area-officer');
        $farmOfficer = Role::findByName('farm-officer');
        $fieldEmployee = Role::findByName('field-employee');

        // $manager = Role::findByName('manager');
        // $staff = Role::findByName('staff');

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

        /*
        |--------------------------------------------------------------------------
        | Farm Officer
        |--------------------------------------------------------------------------
        */

        // $farmOfficer->givePermissionTo([
        //     'farm.view',
        //     'farm.create',
        //     'farm.edit',

        //     'attendance.view',
        //     'attendance.create',

        //     'location.view',
        // ]);

        /*
        |--------------------------------------------------------------------------
        | Field Employee
        |--------------------------------------------------------------------------
        */

        $fieldEmployee->givePermissionTo([
            'farm.view',

            'attendance.view',
            'attendance.create',

            'location.view',
            'location.create',
        ]);

        /*
        |--------------------------------------------------------------------------
        | General Manager
        |--------------------------------------------------------------------------
        */

        // $manager->givePermissionTo([
        //     'user.view',
        //     'company.view',
        //     'company.create',
        //     'company.edit',

        //     'farm.view',
        //     'farm.create',
        //     'farm.edit',

        //     'attendance.view',
        //     'location.view',
        // ]);

        // /*
        // |--------------------------------------------------------------------------
        // | Staff
        // |--------------------------------------------------------------------------
        // */

        // $staff->givePermissionTo([
        //     'company.view',
        //     'farm.view',
        //     'attendance.view',
        //     'location.view',
        // ]);
    }
}

