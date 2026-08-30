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
                'avatar'       => null,
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Green Farm Limited',
                'email'        => 'info@greenfarm.com',
                'phone'        => '01711000002',
                'address'      => 'Sreepur, Gazipur, Bangladesh',
                'avatar'       => null,
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Bangladesh Agro Foods Ltd.',
                'email'        => 'info@bangladeshagro.com',
                'phone'        => '01711000003',
                'address'      => 'Mymensingh, Bangladesh',
                'avatar'       => null,
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Fresh Harvest Agriculture',
                'email'        => 'info@freshharvest.com',
                'phone'        => '01711000004',
                'address'      => 'Rajshahi, Bangladesh',
                'avatar'       => null,
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Delta Agro Industries',
                'email'        => 'info@deltaagro.com',
                'phone'        => '01711000005',
                'address'      => 'Khulna, Bangladesh',
                'avatar'       => null,
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Eastern Farming Solutions',
                'email'        => 'info@easternfarming.com',
                'phone'        => '01711000006',
                'address'      => 'Chattogram, Bangladesh',
                'avatar'       => null,
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Sylhet Green Agriculture',
                'email'        => 'info@sylhetgreen.com',
                'phone'        => '01711000007',
                'address'      => 'Sylhet, Bangladesh',
                'avatar'       => null,
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Northern Agro Limited',
                'email'        => 'info@northernagro.com',
                'phone'        => '01711000008',
                'address'      => 'Rangpur, Bangladesh',
                'avatar'       => null,
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Bangla Dairy & Farm',
                'email'        => 'info@bangladairy.com',
                'phone'        => '01711000009',
                'address'      => 'Tangail, Bangladesh',
                'avatar'       => null,
                'status'       => 'Active',
                'verified'     => 'Yes',
            ],

            [
                'company_name' => 'Golden Harvest Farm',
                'email'        => 'info@goldenharvestfarm.com',
                'phone'        => '01711000010',
                'address'      => 'Bogura, Bangladesh',
                'avatar'       => null,
                'status'       => 'Inactive',
                'verified'     => 'No',
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