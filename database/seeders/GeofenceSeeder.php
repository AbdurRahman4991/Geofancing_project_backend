<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Company;
use App\Models\Geofence;
use Illuminate\Database\Seeder;

class GeofenceSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Get Company
        |--------------------------------------------------------------------------
        */

        $company = Company::first();

        if (!$company) {
            $this->command->error(
                'No company found. Please run CompanySeeder first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Get Areas
        |--------------------------------------------------------------------------
        */

        $areas = Area::with(
            'territory.subDistrict.district.division'
        )->where('status', true)->get();

        if ($areas->isEmpty()) {
            $this->command->error(
                'No areas found. Please run BangladeshLocationSeeder and TerritoryAreaSeeder first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Create Geofences
        |--------------------------------------------------------------------------
        */

        foreach ($areas as $area) {

            /*
            |--------------------------------------------------------------------------
            | Generate realistic coordinates
            |--------------------------------------------------------------------------
            |
            | এখানে demo coordinates ব্যবহার করা হচ্ছে।
            | Production-এ actual farm/company GPS coordinates ব্যবহার করবে।
            |
            */

            $latitude = 23.8103000;
            $longitude = 90.4125000;

            /*
            |--------------------------------------------------------------------------
            | Create Geofence
            |--------------------------------------------------------------------------
            */

            Geofence::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'area_id' => $area->id,
                ],
                [
                    'firm_name' => $company->name . ' - ' . $area->name,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'radius' => 100,
                ]
            );
        }

        $this->command->info(
            $areas->count() . ' geofences created successfully.'
        );
    }
}