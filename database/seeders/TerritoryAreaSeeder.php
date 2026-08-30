<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\SubDistrict;
use App\Models\Territory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TerritoryAreaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | Get all Sub Districts
            |--------------------------------------------------------------------------
            */

            $subDistricts = SubDistrict::with('district.division')
                ->where('status', true)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Realistic Territory Names
            |--------------------------------------------------------------------------
            |
            | এগুলো business/farming operational territory হিসেবে ব্যবহার করা হবে।
            |
            */

            $territoryTypes = [
                'North Territory',
                'South Territory',
                'East Territory',
                'West Territory',
            ];

            /*
            |--------------------------------------------------------------------------
            | Area Names
            |--------------------------------------------------------------------------
            */

            $areaTypes = [
                'Main Area',
                'North Area',
                'South Area',
                'East Area',
                'West Area',
            ];

            foreach ($subDistricts as $subDistrict) {

                /*
                |--------------------------------------------------------------------------
                | Create Territories
                |--------------------------------------------------------------------------
                */

                foreach ($territoryTypes as $territoryType) {

                    $territoryName =
                        $subDistrict->name . ' ' . $territoryType;

                    $territory = Territory::updateOrCreate(
                        [
                            'sub_district_id' => $subDistrict->id,
                            'name' => $territoryName,
                        ],
                        [
                            'status' => true,
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Create Areas
                    |--------------------------------------------------------------------------
                    */

                    foreach ($areaTypes as $areaType) {

                        $areaName =
                            $subDistrict->name . ' ' . $areaType;

                        Area::updateOrCreate(
                            [
                                'territory_id' => $territory->id,
                                'name' => $areaName,
                            ],
                            [
                                'status' => true,
                            ]
                        );
                    }
                }
            }

            $this->command?->info(
                'Territories and Areas seeded successfully.'
            );
        });
    }
}