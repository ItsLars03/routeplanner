<?php

namespace Database\Seeders;

use App\Models\Stop;
use Illuminate\Database\Seeder;

class StopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stops = [
            ['name' => 'Stop 01', 'address' => 'Stationsweg 12, Harlingen', 'latitude' => 53.1745000, 'longitude' => 5.4201000],
            ['name' => 'Stop 02', 'address' => 'Waddenpromenade 4, Harlingen', 'latitude' => 53.1741000, 'longitude' => 5.4262000],
            ['name' => 'Stop 03', 'address' => 'Voorstraat 88, Harlingen', 'latitude' => 53.1762000, 'longitude' => 5.4179000],
            ['name' => 'Stop 04', 'address' => 'Almenumerweg 6, Harlingen', 'latitude' => 53.1689000, 'longitude' => 5.4268000],
            ['name' => 'Stop 05', 'address' => 'Kimswerderweg 15, Harlingen', 'latitude' => 53.1654000, 'longitude' => 5.4163000],
            ['name' => 'Stop 06', 'address' => 'Harlingerstraatweg 22, Midlum', 'latitude' => 53.1790000, 'longitude' => 5.4472000],
            ['name' => 'Stop 07', 'address' => 'Bolswarderweg 31, Franeker', 'latitude' => 53.1861000, 'longitude' => 5.5383000],
            ['name' => 'Stop 08', 'address' => 'Leeuwarderweg 11, Franeker', 'latitude' => 53.1857000, 'longitude' => 5.5417000],
            ['name' => 'Stop 09', 'address' => 'It Noard 7, Dronryp', 'latitude' => 53.1981000, 'longitude' => 5.6612000],
            ['name' => 'Stop 10', 'address' => 'Middenpaed 3, Sexbierum', 'latitude' => 53.2194000, 'longitude' => 5.4835000],
            ['name' => 'Stop 11', 'address' => 'Buorren 19, Wijnaldum', 'latitude' => 53.2050000, 'longitude' => 5.4697000],
            ['name' => 'Stop 12', 'address' => 'Kade 2, Zurich', 'latitude' => 53.1165000, 'longitude' => 5.3928000],
            ['name' => 'Stop 13', 'address' => 'Schoolstraat 10, Kimswerd', 'latitude' => 53.1434000, 'longitude' => 5.4419000],
            ['name' => 'Stop 14', 'address' => 'Hemmemaweg 5, Tzummarum', 'latitude' => 53.2307000, 'longitude' => 5.5436000],
            ['name' => 'Stop 15', 'address' => 'Ludingaweg 14, Harlingen', 'latitude' => 53.1817000, 'longitude' => 5.3951000],
            ['name' => 'Stop 16', 'address' => 'Noorderhaven 1, Harlingen', 'latitude' => 53.1730000, 'longitude' => 5.4147000],
            ['name' => 'Stop 17', 'address' => 'Simon Stijlstraat 9, Harlingen', 'latitude' => 53.1702000, 'longitude' => 5.4283000],
            ['name' => 'Stop 18', 'address' => 'Achlumerdijk 44, Achlum', 'latitude' => 53.1485000, 'longitude' => 5.6129000],
            ['name' => 'Stop 19', 'address' => 'Tsjerkepaed 8, Arum', 'latitude' => 53.1459000, 'longitude' => 5.5452000],
            ['name' => 'Stop 20', 'address' => 'Kleine Buren 27, Makkum', 'latitude' => 53.0561000, 'longitude' => 5.4025000],
        ];

        foreach ($stops as $stop) {
            Stop::firstOrCreate(
                ['name' => $stop['name']],
                [
                    'address' => $stop['address'],
                    'latitude' => $stop['latitude'],
                    'longitude' => $stop['longitude'],
                    'is_active' => true,
                ]
            );
        }
    }
}
