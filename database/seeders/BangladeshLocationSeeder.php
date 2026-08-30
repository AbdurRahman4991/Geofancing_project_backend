<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use App\Models\Division;
use App\Models\District;
use App\Models\SubDistrict;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BangladeshLocationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | Country
            |--------------------------------------------------------------------------
            */

            $country = Country::updateOrCreate(
                ['code' => 'BD'],
                [
                    'name' => 'Bangladesh',
                    'status' => true,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Region + Zone Mapping
            |--------------------------------------------------------------------------
            |
            | Region & Zone are business/operational hierarchy.
            |
            */

            $regionZoneMap = [
                'Dhaka' => [
                    'region' => 'Dhaka Region',
                    'zone'   => 'Central Zone',
                ],

                'Chattogram' => [
                    'region' => 'Chattogram Region',
                    'zone'   => 'South-East Zone',
                ],

                'Rajshahi' => [
                    'region' => 'Rajshahi Region',
                    'zone'   => 'North-West Zone',
                ],

                'Khulna' => [
                    'region' => 'Khulna Region',
                    'zone'   => 'South-West Zone',
                ],

                'Barishal' => [
                    'region' => 'Barishal Region',
                    'zone'   => 'South Zone',
                ],

                'Sylhet' => [
                    'region' => 'Sylhet Region',
                    'zone'   => 'North-East Zone',
                ],

                'Rangpur' => [
                    'region' => 'Rangpur Region',
                    'zone'   => 'North Zone',
                ],

                'Mymensingh' => [
                    'region' => 'Mymensingh Region',
                    'zone'   => 'North-Central Zone',
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | Bangladesh Location Data
            |--------------------------------------------------------------------------
            |
            | Division
            |     District
            |         Upazila / Sub District
            |
            */

            $locations = [

                /*
                |--------------------------------------------------------------------------
                | Dhaka Division
                |--------------------------------------------------------------------------
                */

                'Dhaka' => [

                    'Dhaka' => [
                        'Dhamrai',
                        'Dohar',
                        'Keraniganj',
                        'Nawabganj',
                        'Savar',
                    ],

                    'Faridpur' => [
                        'Alfadanga',
                        'Bhanga',
                        'Boalmari',
                        'Charbhadrasan',
                        'Faridpur Sadar',
                        'Madhukhali',
                        'Nagarkanda',
                        'Sadarpur',
                        'Saltha',
                    ],

                    'Gazipur' => [
                        'Gazipur Sadar',
                        'Kaliakair',
                        'Kaliganj',
                        'Kapasia',
                        'Sreepur',
                    ],

                    'Gopalganj' => [
                        'Gopalganj Sadar',
                        'Kashiani',
                        'Kotalipara',
                        'Muksudpur',
                        'Tungipara',
                    ],

                    'Kishoreganj' => [
                        'Austagram',
                        'Bajitpur',
                        'Bhairab',
                        'Hossainpur',
                        'Itna',
                        'Karimganj',
                        'Katiadi',
                        'Kishoreganj Sadar',
                        'Kuliarchar',
                        'Mithamain',
                        'Nikli',
                        'Pakundia',
                        'Tarail',
                    ],

                    'Madaripur' => [
                        'Dasar',
                        'Kalkini',
                        'Madaripur Sadar',
                        'Rajoir',
                        'Shibchar',
                    ],

                    'Manikganj' => [
                        'Daulatpur',
                        'Ghior',
                        'Harirampur',
                        'Manikganj Sadar',
                        'Saturia',
                        'Shibalaya',
                        'Singair',
                    ],

                    'Munshiganj' => [
                        'Gazaria',
                        'Lohajang',
                        'Munshiganj Sadar',
                        'Sirajdikhan',
                        'Sreenagar',
                        'Tongibari',
                    ],

                    'Narayanganj' => [
                        'Araihazar',
                        'Bandar',
                        'Narayanganj Sadar',
                        'Rupganj',
                        'Sonargaon',
                    ],

                    'Narsingdi' => [
                        'Belabo',
                        'Monohardi',
                        'Narsingdi Sadar',
                        'Palash',
                        'Raipura',
                        'Shibpur',
                    ],

                    'Rajbari' => [
                        'Baliakandi',
                        'Goalanda',
                        'Kalukhali',
                        'Pangsha',
                        'Rajbari Sadar',
                    ],

                    'Shariatpur' => [
                        'Bhedarganj',
                        'Damudya',
                        'Gosairhat',
                        'Naria',
                        'Shariatpur Sadar',
                        'Zajira',
                    ],

                    'Tangail' => [
                        'Basail',
                        'Bhuapur',
                        'Delduar',
                        'Dhanbari',
                        'Ghatail',
                        'Gopalpur',
                        'Kalihati',
                        'Madhupur',
                        'Mirzapur',
                        'Nagarpur',
                        'Sakhipur',
                        'Tangail Sadar',
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Chattogram Division
                |--------------------------------------------------------------------------
                */

                'Chattogram' => [

                    'Bandarban' => [
                        'Ali Kadam',
                        'Bandarban Sadar',
                        'Lama',
                        'Naikhongchhari',
                        'Rowangchhari',
                        'Ruma',
                        'Thanchi',
                    ],

                    'Brahmanbaria' => [
                        'Akhaura',
                        'Ashuganj',
                        'Bancharampur',
                        'Brahmanbaria Sadar',
                        'Bijoynagar',
                        'Kasba',
                        'Nabinagar',
                        'Nasirnagar',
                        'Sarail',
                    ],

                    'Chandpur' => [
                        'Chandpur Sadar',
                        'Faridganj',
                        'Haimchar',
                        'Haziganj',
                        'Kachua',
                        'Matlab Dakshin',
                        'Matlab Uttar',
                        'Shahrasti',
                    ],

                    'Chattogram' => [
                        'Anwara',
                        'Banshkhali',
                        'Boalkhali',
                        'Chandanaish',
                        'Fatikchhari',
                        'Hathazari',
                        'Lohagara',
                        'Mirsharai',
                        'Patiya',
                        'Rangunia',
                        'Raozan',
                        'Sandwip',
                        'Satkania',
                        'Sitakunda',
                    ],

                    'Cumilla' => [
                        'Barura',
                        'Brahmanpara',
                        'Burichang',
                        'Chandina',
                        'Chauddagram',
                        'Comilla Adarsha Sadar',
                        'Daudkandi',
                        'Debidwar',
                        'Homna',
                        'Laksam',
                        'Lalmai',
                        'Meghna',
                        'Monohargonj',
                        'Muradnagar',
                        'Nangalkot',
                        'Titas',
                    ],

                    "Cox's Bazar" => [
                        'Chakaria',
                        'Cox’s Bazar Sadar',
                        'Eidgaon',
                        'Kutubdia',
                        'Maheshkhali',
                        'Pekua',
                        'Ramu',
                        'Teknaf',
                        'Ukhia',
                    ],

                    'Feni' => [
                        'Chhagalnaiya',
                        'Daganbhuiyan',
                        'Feni Sadar',
                        'Fulgazi',
                        'Parshuram',
                        'Sonagazi',
                    ],

                    'Khagrachhari' => [
                        'Dighinala',
                        'Khagrachhari Sadar',
                        'Lakshmichhari',
                        'Mahalchhari',
                        'Manikchhari',
                        'Matiranga',
                        'Panchhari',
                        'Ramgarh',
                    ],

                    'Lakshmipur' => [
                        'Kamalnagar',
                        'Lakshmipur Sadar',
                        'Raipur',
                        'Ramganj',
                        'Ramgati',
                    ],

                    'Noakhali' => [
                        'Begumganj',
                        'Chatkhil',
                        'Companiganj',
                        'Hatiya',
                        'Kabirhat',
                        'Senbagh',
                        'Sonaimuri',
                        'Subarnachar',
                        'Noakhali Sadar',
                    ],

                    'Rangamati' => [
                        'Baghaichhari',
                        'Barkal',
                        'Belaichhari',
                        'Juraichhari',
                        'Kaptai',
                        'Kaukhali',
                        'Langadu',
                        'Naniarchar',
                        'Rajasthali',
                        'Rangamati Sadar',
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Rajshahi Division
                |--------------------------------------------------------------------------
                */

                'Rajshahi' => [

                    'Bogura' => [
                        'Adamdighi',
                        'Bogura Sadar',
                        'Dhunat',
                        'Dhupchanchia',
                        'Gabtali',
                        'Kahaloo',
                        'Nandigram',
                        'Sariakandi',
                        'Shajahanpur',
                        'Sherpur',
                        'Shibganj',
                        'Sonatala',
                    ],

                    'Chapainawabganj' => [
                        'Bholahat',
                        'Chapainawabganj Sadar',
                        'Gomastapur',
                        'Nachole',
                        'Shibganj',
                    ],

                    'Joypurhat' => [
                        'Akkelpur',
                        'Joypurhat Sadar',
                        'Kalai',
                        'Khetlal',
                        'Panchbibi',
                    ],

                    'Naogaon' => [
                        'Atrai',
                        'Badalgachhi',
                        'Dhamoirhat',
                        'Manda',
                        'Mahadebpur',
                        'Naogaon Sadar',
                        'Niamatpur',
                        'Patnitala',
                        'Porsha',
                        'Raninagar',
                        'Sapahar',
                    ],

                    'Natore' => [
                        'Bagatipara',
                        'Baraigram',
                        'Gurudaspur',
                        'Lalpur',
                        'Natore Sadar',
                        'Singra',
                    ],

                    'Pabna' => [
                        'Atgharia',
                        'Bera',
                        'Bhangura',
                        'Chatmohar',
                        'Faridpur',
                        'Ishwardi',
                        'Pabna Sadar',
                        'Santhia',
                        'Sujanagar',
                    ],

                    'Rajshahi' => [
                        'Bagha',
                        'Bagmara',
                        'Boalia',
                        'Charghat',
                        'Durgapur',
                        'Godagari',
                        'Mohanpur',
                        'Paba',
                        'Puthia',
                        'Tanore',
                    ],

                    'Sirajganj' => [
                        'Belkuchi',
                        'Chauhali',
                        'Kamarkhanda',
                        'Kazipur',
                        'Raiganj',
                        'Shahjadpur',
                        'Sirajganj Sadar',
                        'Tarash',
                        'Ullapara',
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Khulna Division
                |--------------------------------------------------------------------------
                */

                'Khulna' => [

                    'Bagerhat' => [
                        'Bagerhat Sadar',
                        'Chitalmari',
                        'Fakirhat',
                        'Kachua',
                        'Mollahat',
                        'Mongla',
                        'Morrelganj',
                        'Mollahat',
                        'Rampal',
                        'Sarankhola',
                    ],

                    'Chuadanga' => [
                        'Alamdanga',
                        'Chuadanga Sadar',
                        'Damurhuda',
                        'Jibannagar',
                    ],

                    'Jashore' => [
                        'Abhaynagar',
                        'Bagherpara',
                        'Chaugachha',
                        'Jashore Sadar',
                        'Jhikargachha',
                        'Keshabpur',
                        'Manirampur',
                        'Sharsha',
                    ],

                    'Jhenaidah' => [
                        'Harinakunda',
                        'Jhenaidah Sadar',
                        'Kaliganj',
                        'Kotchandpur',
                        'Maheshpur',
                        'Shailkupa',
                    ],

                    'Khulna' => [
                        'Batiaghata',
                        'Dacope',
                        'Dighalia',
                        'Dumuria',
                        'Koyra',
                        'Paikgachha',
                        'Phultala',
                        'Rupsa',
                        'Terokhada',
                    ],

                    'Kushtia' => [
                        'Bheramara',
                        'Daulatpur',
                        'Khoksa',
                        'Kumarkhali',
                        'Kushtia Sadar',
                        'Mirpur',
                    ],

                    'Magura' => [
                        'Magura Sadar',
                        'Mohammadpur',
                        'Shalikha',
                        'Sreepur',
                    ],

                    'Meherpur' => [
                        'Gangni',
                        'Meherpur Sadar',
                        'Mujibnagar',
                    ],

                    'Narail' => [
                        'Kalia',
                        'Lohagara',
                        'Narail Sadar',
                    ],

                    'Satkhira' => [
                        'Assasuni',
                        'Debhata',
                        'Kalaroa',
                        'Kaliganj',
                        'Satkhira Sadar',
                        'Shyamnagar',
                        'Tala',
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Barishal Division
                |--------------------------------------------------------------------------
                */

                'Barishal' => [

                    'Barguna' => [
                        'Amtali',
                        'Bamna',
                        'Barguna Sadar',
                        'Betagi',
                        'Patharghata',
                        'Taltali',
                    ],

                    'Barishal' => [
                        'Agailjhara',
                        'Babuganj',
                        'Bakerganj',
                        'Banaripara',
                        'Barishal Sadar',
                        'Gournadi',
                        'Hizla',
                        'Mehendiganj',
                        'Muladi',
                        'Wazirpur',
                    ],

                    'Bhola' => [
                        'Bhola Sadar',
                        'Borhanuddin',
                        'Char Fasson',
                        'Daulatkhan',
                        'Lalmohan',
                        'Manpura',
                        'Tazumuddin',
                    ],

                    'Jhalokathi' => [
                        'Jhalokathi Sadar',
                        'Kathalia',
                        'Nalchity',
                        'Rajapur',
                    ],

                    'Patuakhali' => [
                        'Bauphal',
                        'Dashmina',
                        'Dumki',
                        'Galachipa',
                        'Kalapara',
                        'Mirzaganj',
                        'Patuakhali Sadar',
                        'Rangabali',
                    ],

                    'Pirojpur' => [
                        'Bhandaria',
                        'Kaukhali',
                        'Mathbaria',
                        'Nazirpur',
                        'Nesarabad',
                        'Pirojpur Sadar',
                        'Zianagar',
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Sylhet Division
                |--------------------------------------------------------------------------
                */

                'Sylhet' => [

                    'Habiganj' => [
                        'Ajmiriganj',
                        'Bahubal',
                        'Baniachong',
                        'Chunarughat',
                        'Habiganj Sadar',
                        'Lakhai',
                        'Madhabpur',
                        'Nabiganj',
                        'Shayestaganj',
                    ],

                    'Moulvibazar' => [
                        'Barlekha',
                        'Juri',
                        'Kamalganj',
                        'Kulaura',
                        'Moulvibazar Sadar',
                        'Rajnagar',
                        'Sreemangal',
                    ],

                    'Sunamganj' => [
                        'Bishwambharpur',
                        'Chhatak',
                        'Derai',
                        'Dharampasha',
                        'Dowarabazar',
                        'Jagannathpur',
                        'Jamalganj',
                        'Shalla',
                        'Shantiganj',
                        'Sunamganj Sadar',
                        'Tahirpur',
                        'Madhyanagar',
                    ],

                    'Sylhet' => [
                        'Balaganj',
                        'Beanibazar',
                        'Bishwanath',
                        'Companiganj',
                        'Fenchuganj',
                        'Golapganj',
                        'Gowainghat',
                        'Jaintiapur',
                        'Kanaighat',
                        'Osmani Nagar',
                        'Sylhet Sadar',
                        'Zakiganj',
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Rangpur Division
                |--------------------------------------------------------------------------
                */

                'Rangpur' => [

                    'Dinajpur' => [
                        'Birampur',
                        'Birganj',
                        'Biral',
                        'Bochaganj',
                        'Chirirbandar',
                        'Dinajpur Sadar',
                        'Fulbari',
                        'Ghoraghat',
                        'Hakimpur',
                        'Kaharole',
                        'Khansama',
                        'Nawabganj',
                        'Parbatipur',
                    ],

                    'Gaibandha' => [
                        'Fulchhari',
                        'Gaibandha Sadar',
                        'Gobindaganj',
                        'Palashbari',
                        'Sadullapur',
                        'Saghata',
                        'Sundarganj',
                    ],

                    'Kurigram' => [
                        'Bhurungamari',
                        'Char Rajibpur',
                        'Chilmari',
                        'Kurigram Sadar',
                        'Nageshwari',
                        'Phulbari',
                        'Rajarhat',
                        'Raomari',
                        'Ulipur',
                    ],

                    'Lalmonirhat' => [
                        'Aditmari',
                        'Hatibandha',
                        'Kaliganj',
                        'Lalmonirhat Sadar',
                        'Patgram',
                    ],

                    'Nilphamari' => [
                        'Dimla',
                        'Domar',
                        'Jaldhaka',
                        'Kishoreganj',
                        'Nilphamari Sadar',
                        'Saidpur',
                    ],

                    'Panchagarh' => [
                        'Atwari',
                        'Boda',
                        'Debiganj',
                        'Panchagarh Sadar',
                        'Tetulia',
                    ],

                    'Rangpur' => [
                        'Badarganj',
                        'Gangachara',
                        'Kaunia',
                        'Mithapukur',
                        'Pirganj',
                        'Pirgachha',
                        'Rangpur Sadar',
                        'Taraganj',
                    ],

                    'Thakurgaon' => [
                        'Baliadangi',
                        'Haripur',
                        'Pirganj',
                        'Ranisankail',
                        'Thakurgaon Sadar',
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Mymensingh Division
                |--------------------------------------------------------------------------
                */

                'Mymensingh' => [

                    'Jamalpur' => [
                        'Bakshiganj',
                        'Dewanganj',
                        'Islampur',
                        'Jamalpur Sadar',
                        'Madarganj',
                        'Melandaha',
                        'Sarishabari',
                    ],

                    'Mymensingh' => [
                        'Bhaluka',
                        'Dhobaura',
                        'Fulbaria',
                        'Gaffargaon',
                        'Gauripur',
                        'Haluaghat',
                        'Ishwarganj',
                        'Muktagachha',
                        'Mymensingh Sadar',
                        'Nandail',
                        'Phulpur',
                        'Tarakanda',
                        'Trishal',
                    ],

                    'Netrokona' => [
                        'Atpara',
                        'Barhatta',
                        'Durgapur',
                        'Khaliajuri',
                        'Kalmakanda',
                        'Kendua',
                        'Madan',
                        'Mohanganj',
                        'Netrokona Sadar',
                        'Purbadhala',
                    ],

                    'Sherpur' => [
                        'Jhenaigati',
                        'Nakla',
                        'Nalitabari',
                        'Sherpur Sadar',
                        'Sreebardi',
                    ],
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | Insert Location Data
            |--------------------------------------------------------------------------
            */

            foreach ($locations as $divisionName => $districts) {

                if (! isset($regionZoneMap[$divisionName])) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Region
                |--------------------------------------------------------------------------
                */

                $region = Region::updateOrCreate(
                    [
                        'country_id' => $country->id,
                        'name' => $regionZoneMap[$divisionName]['region'],
                    ],
                    [
                        'status' => true,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Zone
                |--------------------------------------------------------------------------
                */

                $zone = Zone::updateOrCreate(
                    [
                        'region_id' => $region->id,
                        'name' => $regionZoneMap[$divisionName]['zone'],
                    ],
                    [
                        'status' => true,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Division
                |--------------------------------------------------------------------------
                */

                $division = Division::updateOrCreate(
                    [
                        'zone_id' => $zone->id,
                        'name' => $divisionName,
                    ],
                    [
                        'status' => true,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | District
                |--------------------------------------------------------------------------
                */

                foreach ($districts as $districtName => $subDistricts) {

                    $district = District::updateOrCreate(
                        [
                            'division_id' => $division->id,
                            'name' => $districtName,
                        ],
                        [
                            'status' => true,
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Sub District / Upazila
                    |--------------------------------------------------------------------------
                    */

                    foreach ($subDistricts as $subDistrictName) {

                        SubDistrict::updateOrCreate(
                            [
                                'district_id' => $district->id,
                                'name' => $subDistrictName,
                            ],
                            [
                                'status' => true,
                            ]
                        );
                    }
                }
            }

            $this->command?->info(
                'Bangladesh location data seeded successfully.'
            );
        });
    }
}