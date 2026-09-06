<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Company;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            $this->command->error('No company found. Please seed companies first.');
            return;
        }

        $employees = [
            [
                'name' => 'Rahim Ahmed',
                'employee_id' => 'EMP-1001',
                'company_id' => $company->id,
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
                'employee_id' => 'EMP-1002',
                'company_id' => $company->id,
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
                'employee_id' => 'EMP-1003',
                'company_id' => $company->id,
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
                'employee_id' => 'EMP-1004',
                'company_id' => $company->id,
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
                'employee_id' => 'EMP-1005',
                'company_id' => $company->id,
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
                'employee_id' => 'EMP-1006',
                'company_id' => $company->id,
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
                'employee_id' => 'EMP-1007',
                'company_id' => $company->id,
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
                'employee_id' => 'EMP-1008',
                'company_id' => $company->id,
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
                'employee_id' => 'EMP-1009',
                'company_id' => $company->id,
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

        foreach ($employees as $employee) {
            Employee::updateOrCreate(
                [
                    'employee_id' => $employee['employee_id'],
                ],
                $employee
            );
        }

        $this->command->info('Employees seeded successfully.');
    }
}
