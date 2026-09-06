<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@gmail.com',
                'phone' => '01814874980',
                'employee_id' => '901825',
                'role' => 'super-admin',
            ],

            [
                'name' => 'Country Manager',
                'email' => 'country.manager@gmail.com',
                'phone' => '01814874981',
                'employee_id' => '901826',
                'role' => 'country-manager',
            ],

            [
                'name' => 'Regional Manager',
                'email' => 'regional.manager@gmail.com',
                'phone' => '01814874982',
                'employee_id' => '901827',
                'role' => 'regional-manager',
            ],

            [
                'name' => 'Zone Manager',
                'email' => 'zone.manager@gmail.com',
                'phone' => '01814874983',
                'employee_id' => '901828',
                'role' => 'zone-manager',
            ],

            [
                'name' => 'Division Manager',
                'email' => 'division.manager@gmail.com',
                'phone' => '01814874984',
                'employee_id' => '901829',
                'role' => 'division-manager',
            ],

            [
                'name' => 'District Manager',
                'email' => 'district.manager@gmail.com',
                'phone' => '01814874985',
                'employee_id' => '901830',
                'role' => 'district-manager',
            ],

            [
                'name' => 'Sub District Manager',
                'email' => 'subdistrict.manager@gmail.com',
                'phone' => '01814874986',
                'employee_id' => '901831',
                'role' => 'sub-district-manager',
            ],

            [
                'name' => 'Territory Manager',
                'email' => 'territory.manager@gmail.com',
                'phone' => '01814874987',
                'employee_id' => '901832',
                'role' => 'territory-manager',
            ],

            [
                'name' => 'Area Officer',
                'email' => 'area.officer@gmail.com',
                'phone' => '01814874988',
                'employee_id' => '901833',
                'role' => 'area-officer',
            ],

            [
                'name' => 'Field Employee',
                'email' => 'field.employee@gmail.com',
                'phone' => '01814874989',
                'employee_id' => '901834',
                'role' => 'field-employee',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];

            unset($data['role']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'employee_id' => $data['employee_id'],
                    'password' => bcrypt('password'),
                ]
            );

            $user->syncRoles([$role]);
        }
    }
}


