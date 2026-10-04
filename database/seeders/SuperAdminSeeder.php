<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Get First Company
        |--------------------------------------------------------------------------
        */

        $company = Company::orderBy('id')->first();

        if (!$company) {
            $this->command->error(
                'No company found. Please run CompanySeeder first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Create / Update Super Admin Role
        |--------------------------------------------------------------------------
        |
        | Your application is using Sanctum guard.
        |
        */

        $role = Role::firstOrCreate(
            [
                'name' => 'Super-Admin',
                'guard_name' => 'sanctum',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Create / Update Super Admin Employee
        |--------------------------------------------------------------------------
        */

        $employee = Employee::updateOrCreate(
            [
                'employee_id' => 'SUPER-ADMIN-001',
            ],
            [
                'company_id' => $company->id,
                'name' => 'Super Admin',
                'phone' => '01814874980',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department' => 'Management',
                'unit' => 'Head Office',
                'date_of_joining' => now()->format('Y-m-d'),
                'division' => 'Dhaka',
                'designation' => 'Super Admin',
                'reporting_person' => null,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Create / Update Super Admin User
        |--------------------------------------------------------------------------
        */

        $user = User::updateOrCreate(
            [
                'email' => 'admin@gmail.com',
            ],
            [
                'name' => 'Super Admin',
                'email' => 'admin@gmail.com',
                'phone' => '01814874980',
                'employee_id' => $employee->id,
                'company_id' => $company->id,
                'password' => Hash::make('12345678'),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Assign Super Admin Role
        |--------------------------------------------------------------------------
        */

        $user->syncRoles([$role]);

        /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

        $this->command->newLine();

        $this->command->info(
            'Super Admin created successfully.'
        );

        $this->command->info(
            "Employee ID: {$employee->employee_id}"
        );

        $this->command->info(
            "User ID: {$user->id}"
        );

        $this->command->info(
            'Email: admin@gmail.com'
        );

        $this->command->info(
            'Password: 12345678'
        );

        $this->command->info(
            'Role: Super-Admin'
        );

        $this->command->info(
            'Guard: sanctum'
        );

        $this->command->newLine();
    }
}
