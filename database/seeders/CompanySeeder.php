<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [

            [
                'company_name' => 'Aman Group Limited',
                'email'        => 'info@amangroup.com',
                'phone'        => '01711000001',
                'address'      => 'Gulshan-1, Dhaka, Bangladesh',                
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Green Farm Limited',
                'email'        => 'info@greenfarm.com',
                'phone'        => '01711000002',
                'address'      => 'Sreepur, Gazipur, Bangladesh',                
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Bangladesh Agro Foods Ltd.',
                'email'        => 'info@bangladeshagro.com',
                'phone'        => '01711000003',
                'address'      => 'Mymensingh, Bangladesh',                
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Fresh Harvest Agriculture',
                'email'        => 'info@freshharvest.com',
                'phone'        => '01711000004',
                'address'      => 'Rajshahi, Bangladesh',                
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Delta Agro Industries',
                'email'        => 'info@deltaagro.com',
                'phone'        => '01711000005',
                'address'      => 'Khulna, Bangladesh',                
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],
            
        ];

        foreach ($companies as $company) {

            Company::updateOrCreate(
                [
                    'email' => $company['email'],
                ],
                $company
            );
        }

        $this->command?->info(
            count($companies) . ' companies seeded successfully.'
        );
    }
}