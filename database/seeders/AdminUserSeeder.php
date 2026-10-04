<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Get All Employees
        |--------------------------------------------------------------------------
        */

        $employees = Employee::all();

        if ($employees->isEmpty()) {
            $this->command->error(
                'No employees found. Please run EmployeeSeeder first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Counters
        |--------------------------------------------------------------------------
        */

        $userCount = 0;
        $skippedCount = 0;

        /*
        |--------------------------------------------------------------------------
        | Create / Update Users
        |--------------------------------------------------------------------------
        */

        foreach ($employees as $employee) {

            /*
            |--------------------------------------------------------------------------
            | Skip Super Admin
            |--------------------------------------------------------------------------
            |
            | Super Admin is created separately by SuperAdminSeeder.
            |
            */

            if ($employee->designation === 'Super Admin') {

                $this->command->line(
                    "Skipped Super Admin: {$employee->name}"
                );

                $skippedCount++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Skip Field Employee
            |--------------------------------------------------------------------------
            |
            | Field Employee does not have a system role/user.
            |
            */

            if ($employee->designation === 'Field Employee') {

                $this->command->line(
                    "Skipped Field Employee: {$employee->name}"
                );

                $skippedCount++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Get Role
            |--------------------------------------------------------------------------
            */

            $role = $this->getRoleByDesignation(
                $employee->designation
            );

            /*
            |--------------------------------------------------------------------------
            | Generate Email
            |--------------------------------------------------------------------------
            */

            $email = strtolower(
                str_replace(' ', '.', trim($employee->name))
            ) . '.' . $employee->company_id . '@example.com';

            /*
            |--------------------------------------------------------------------------
            | Create / Update User
            |--------------------------------------------------------------------------
            */

            $user = User::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                ],
                [
                    'name' => $employee->name,
                    'email' => $email,
                    'phone' => $employee->phone,
                    'employee_id' => $employee->id,
                    'company_id' => $employee->company_id,
                    'password' => bcrypt('password'),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Assign Role
            |--------------------------------------------------------------------------
            */

            $user->syncRoles([
                $role
            ]);

            $userCount++;

            /*
            |--------------------------------------------------------------------------
            | Console Output
            |--------------------------------------------------------------------------
            */

            $this->command->line(
                "User created/updated: {$employee->name} → {$role}"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $this->command->newLine();

        $this->command->info(
            "{$userCount} users created/updated successfully."
        );

        $this->command->info(
            "{$skippedCount} employees skipped."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Get Role By Employee Designation
    |--------------------------------------------------------------------------
    */

    private function getRoleByDesignation(?string $designation): string
    {
        return match ($designation) {

            /*
            |--------------------------------------------------------------------------
            | Country
            |--------------------------------------------------------------------------
            */

            'Country Manager'
                => 'country-manager',

            /*
            |--------------------------------------------------------------------------
            | Region
            |--------------------------------------------------------------------------
            */

            'Regional Manager'
                => 'regional-manager',

            /*
            |--------------------------------------------------------------------------
            | Zone
            |--------------------------------------------------------------------------
            */

            'Zone Manager'
                => 'zone-manager',

            /*
            |--------------------------------------------------------------------------
            | Division
            |--------------------------------------------------------------------------
            */

            'Division Manager'
                => 'division-manager',

            /*
            |--------------------------------------------------------------------------
            | District
            |--------------------------------------------------------------------------
            */

            'District Manager'
                => 'district-manager',

            /*
            |--------------------------------------------------------------------------
            | Sub District
            |--------------------------------------------------------------------------
            */

            'Sub District Manager'
                => 'sub-district-manager',

            /*
            |--------------------------------------------------------------------------
            | Territory
            |--------------------------------------------------------------------------
            */

            'Territory Manager'
                => 'territory-manager',

            /*
            |--------------------------------------------------------------------------
            | Area
            |--------------------------------------------------------------------------
            */

            'Area Officer'
                => 'area-officer',

            /*
            |--------------------------------------------------------------------------
            | Unknown Designation
            |--------------------------------------------------------------------------
            */

            default
                => throw new \RuntimeException(
                    "No role mapping found for designation: {$designation}"
                ),
        };
    }
}