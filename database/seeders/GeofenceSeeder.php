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
        | Get All Companies
        |--------------------------------------------------------------------------
        */

        $companies = Company::orderBy('id')->get();

        if ($companies->isEmpty()) {
            $this->command->error(
                'No companies found. Please run CompanySeeder first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Get All Active Areas
        |--------------------------------------------------------------------------
        |
        | Area is part of hierarchy.
        | Area does NOT need company_id.
        |
        */

        $areas = Area::where('status', true)->get();

        if ($areas->isEmpty()) {
            $this->command->error(
                'No active areas found. Please run AreaSeeder first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Create Geofence Company Wise
        |--------------------------------------------------------------------------
        */

        $totalGeofences = 0;

        foreach ($companies as $company) {

            /*
            |--------------------------------------------------------------------------
            | Company ID
            |--------------------------------------------------------------------------
            */

            $companyId = $company->id;

            /*
            |--------------------------------------------------------------------------
            | Create Geofence for Every Area
            |--------------------------------------------------------------------------
            */

            foreach ($areas as $area) {

                /*
                |--------------------------------------------------------------------------
                | Demo Coordinates
                |--------------------------------------------------------------------------
                */

                $latitude = 23.8103000;
                $longitude = 90.4125000;

                /*
                |--------------------------------------------------------------------------
                | Create / Update Geofence
                |--------------------------------------------------------------------------
                */

                Geofence::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'area_id' => $area->id,
                    ],
                    [
                        'firm_name' => $company->company_name . ' - ' . $area->name,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'radius' => 100,
                    ]
                );

                $totalGeofences++;
            }

            $this->command->info(
                "Geofences seeded for company ID: {$companyId} ({$company->company_name})"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

        $this->command->info(
            "{$totalGeofences} geofences created/updated successfully."
        );

        $this->command->info(
            "Total companies: {$companies->count()}"
        );

        $this->command->info(
            "Total active areas: {$areas->count()}"
        );
    }
}