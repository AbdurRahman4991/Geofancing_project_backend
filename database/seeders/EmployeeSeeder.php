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
                'department_id' => 'Management',
                'unit_id' => 'Head Office',
                'date_of_joining' => '2024-01-01',
                'division_id' => 'Dhaka',
                'designation_id' => 'Country Manager',
                'reporting_person' => 'Super Admin',
            ],

            [
                'name' => 'Karim Hasan',
                'phone' => '01811111112',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department_id' => 'Regional Management',
                'unit_id' => 'Dhaka Region',
                'date_of_joining' => '2024-02-01',
                'division_id' => 'Dhaka',
                'designation_id' => 'Regional Manager',
                'reporting_person' => 'Rahim Ahmed',
            ],

            [
                'name' => 'Sabbir Hossain',
                'phone' => '01811111113',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department_id' => 'Zone Management',
                'unit_id' => 'Dhaka Zone',
                'date_of_joining' => '2024-03-01',
                'division_id' => 'Dhaka',
                'designation_id' => 'Zone Manager',
                'reporting_person' => 'Karim Hasan',
            ],

            [
                'name' => 'Mehedi Hasan',
                'phone' => '01811111114',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department_id' => 'Division Management',
                'unit_id' => 'Dhaka Division',
                'date_of_joining' => '2024-04-01',
                'division_id' => 'Dhaka',
                'designation_id' => 'Division Manager',
                'reporting_person' => 'Sabbir Hossain',
            ],

            [
                'name' => 'Hasan Mahmud',
                'phone' => '01811111115',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department_id' => 'District Management',
                'unit_id' => 'Dhaka District',
                'date_of_joining' => '2024-05-01',
                'division_id' => 'Dhaka',
                'designation_id' => 'District Manager',
                'reporting_person' => 'Mehedi Hasan',
            ],

            [
                'name' => 'Nayeem Islam',
                'phone' => '01811111116',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department_id' => 'Sub District Management',
                'unit_id' => 'Savar',
                'date_of_joining' => '2024-06-01',
                'division_id' => 'Dhaka',
                'designation_id' => 'Sub District Manager',
                'reporting_person' => 'Hasan Mahmud',
            ],

            [
                'name' => 'Rasel Mia',
                'phone' => '01811111117',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department_id' => 'Territory Management',
                'unit_id' => 'Savar Territory',
                'date_of_joining' => '2024-07-01',
                'division_id' => 'Dhaka',
                'designation_id' => 'Territory Manager',
                'reporting_person' => 'Nayeem Islam',
            ],

            [
                'name' => 'Shakil Ahmed',
                'phone' => '01811111118',
                'status' => 'active',
                'nature_of_employment' => 'contract',
                'department_id' => 'Field Operations',
                'unit_id' => 'Savar Area',
                'date_of_joining' => '2024-08-01',
                'division_id' => 'Dhaka',
                'designation_id' => 'Area Officer',
                'reporting_person' => 'Rasel Mia',
            ],

            [
                'name' => 'Imran Hossain',
                'phone' => '01811111119',
                'status' => 'active',
                'nature_of_employment' => 'permanent',
                'department_id' => 'Field Operations',
                'unit_id' => 'Savar Area',
                'date_of_joining' => '2024-09-01',
                'division_id' => 'Dhaka',
                'designation_id' => 'Field Employee',
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
                        'department_id' => $template['department_id'],
                        'unit_id' => $template['unit_id'],
                        'date_of_joining' => $template['date_of_joining'],
                        'division_id' => $template['division_id'],
                        'designation_id' => $template['designation_id'],
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