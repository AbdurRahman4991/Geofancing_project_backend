<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Company;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Get All Companies
        |--------------------------------------------------------------------------
        */

        $companies = Company::orderBy('id')->get();

        if ($companies->isEmpty()) {
            $this->command->error(
                'No companies found. Please seed companies first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Base Employee Data
        |--------------------------------------------------------------------------
        */

        $employeeTemplates = [
            [
                'name' => 'Rahim Ahmed',
                'phone' => '01811111111',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department' => 'Management',
                'unit' => 'Head Office',
                'date_of_joining' => '2024-01-01',
                'division' => 'Dhaka',
                'designation' => 'Country Manager',
                'reporting_person' => 'Super Admin',
            ],

            [
                'name' => 'Karim Hasan',
                'phone' => '01811111112',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department' => 'Regional Management',
                'unit' => 'Dhaka Region',
                'date_of_joining' => '2024-02-01',
                'division' => 'Dhaka',
                'designation' => 'Regional Manager',
                'reporting_person' => 'Rahim Ahmed',
            ],

            [
                'name' => 'Sabbir Hossain',
                'phone' => '01811111113',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department' => 'Zone Management',
                'unit' => 'Dhaka Zone',
                'date_of_joining' => '2024-03-01',
                'division' => 'Dhaka',
                'designation' => 'Zone Manager',
                'reporting_person' => 'Karim Hasan',
            ],

            [
                'name' => 'Mehedi Hasan',
                'phone' => '01811111114',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department' => 'Division Management',
                'unit' => 'Dhaka Division',
                'date_of_joining' => '2024-04-01',
                'division' => 'Dhaka',
                'designation' => 'Division Manager',
                'reporting_person' => 'Sabbir Hossain',
            ],

            [
                'name' => 'Hasan Mahmud',
                'phone' => '01811111115',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department' => 'District Management',
                'unit' => 'Dhaka District',
                'date_of_joining' => '2024-05-01',
                'division' => 'Dhaka',
                'designation' => 'District Manager',
                'reporting_person' => 'Mehedi Hasan',
            ],

            [
                'name' => 'Nayeem Islam',
                'phone' => '01811111116',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department' => 'Sub District Management',
                'unit' => 'Savar',
                'date_of_joining' => '2024-06-01',
                'division' => 'Dhaka',
                'designation' => 'Sub District Manager',
                'reporting_person' => 'Hasan Mahmud',
            ],

            [
                'name' => 'Rasel Mia',
                'phone' => '01811111117',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department' => 'Territory Management',
                'unit' => 'Savar Territory',
                'date_of_joining' => '2024-07-01',
                'division' => 'Dhaka',
                'designation' => 'Territory Manager',
                'reporting_person' => 'Nayeem Islam',
            ],

            [
                'name' => 'Shakil Ahmed',
                'phone' => '01811111118',
                'status' => 'active',
                'nature_of_employment' => 'contract',
                'department' => 'Field Operations',
                'unit' => 'Savar Area',
                'date_of_joining' => '2024-08-01',
                'division' => 'Dhaka',
                'designation' => 'Area Officer',
                'reporting_person' => 'Rasel Mia',
            ],

            [
                'name' => 'Imran Hossain',
                'phone' => '01811111119',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department' => 'Field Operations',
                'unit' => 'Savar Area',
                'date_of_joining' => '2024-09-01',
                'division' => 'Dhaka',
                'designation' => 'Field Employee',
                'reporting_person' => 'Shakil Ahmed',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Employees For Every Company
        |--------------------------------------------------------------------------
        */

        foreach ($companies as $company) {

            /*
            |--------------------------------------------------------------------------
            | Employee ID Prefix
            |--------------------------------------------------------------------------
            |
            | Company ID 1 → EMP-1001
            | Company ID 2 → EMP-2001
            | Company ID 3 → EMP-3001
            |
            */

            $employeeStartNumber = ($company->id * 1000) + 1;

            foreach ($employeeTemplates as $index => $template) {

                $employeeId = 'EMP-' . ($employeeStartNumber + $index);

                Employee::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                    ],
                    [
                        'company_id' => $company->id,
                        'name' => $template['name'],
                        'phone' => $template['phone'],
                        'status' => $template['status'],
                        'nature_of_employment' => $template['nature_of_employment'],
                        'department' => $template['department'],
                        'unit' => $template['unit'],
                        'date_of_joining' => $template['date_of_joining'],
                        'division' => $template['division'],
                        'designation' => $template['designation'],
                        'reporting_person' => $template['reporting_person'],
                    ]
                );
            }

            $this->command->info(
                "{$company->company_name} employees seeded successfully."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Final Message
        |--------------------------------------------------------------------------
        */

        $totalEmployees = $companies->count() * count($employeeTemplates);

        $this->command->info(
            "All company employees seeded successfully."
        );

        $this->command->info(
            "Total companies: {$companies->count()}"
        );

        $this->command->info(
            "Total employees: {$totalEmployees}"
        );
    }
}