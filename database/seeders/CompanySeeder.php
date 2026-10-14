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