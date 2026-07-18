<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'phone' => '01814874980',
                'employee_id' =>'901825',
                'password' => bcrypt('password'),
            ]
        );

        $user->assignRole('super-admin');
    }
}
