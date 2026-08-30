<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use App\Models\Division;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\Territory;
use App\Models\Area;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Country
        |--------------------------------------------------------------------------
        */

        $country = Country::create([
            'name' => 'Bangladesh',
            'code' => 'BD',
            'status' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Region
        |--------------------------------------------------------------------------
        */

        $region = Region::create([
            'country_id' => $country->id,
            'name' => 'Dhaka Region',
            'status' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Zone
        |--------------------------------------------------------------------------
        */

        $zone = Zone::create([
            'region_id' => $region->id,
            'name' => 'Central Zone',
            'status' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Division
        |--------------------------------------------------------------------------
        */

        $division = Division::create([
            'zone_id' => $zone->id,
            'name' => 'Dhaka Division',
            'status' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | District
        |--------------------------------------------------------------------------
        */

        $district = District::create([
            'division_id' => $division->id,
            'name' => 'Gazipur',
            'status' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Sub District
        |--------------------------------------------------------------------------
        */

        $subDistrict = SubDistrict::create([
            'district_id' => $district->id,
            'name' => 'Sreepur',
            'status' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Territory
        |--------------------------------------------------------------------------
        */

        $territory = Territory::create([
            'sub_district_id' => $subDistrict->id,
            'name' => 'Sreepur Territory',
            'status' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Area
        |--------------------------------------------------------------------------
        */

        $area = Area::create([
            'territory_id' => $territory->id,
            'name' => 'Sreepur Area',
            'status' => true,
        ]);
    }
}
