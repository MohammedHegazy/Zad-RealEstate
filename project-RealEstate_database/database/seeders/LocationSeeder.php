<?php

namespace Database\Seeders;

use App\Models\Cities;
use App\Models\Places;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $damascus = Cities::query()->updateOrCreate(
            ['name' => 'Damascus'],
            [
                'image' => null,
                'latitude' => 33.5138,
                'longitude' => 36.2765,
            ],
        );

        $aleppo = Cities::query()->updateOrCreate(
            ['name' => 'Aleppo'],
            [
                'image' => null,
                'latitude' => 36.2021,
                'longitude' => 37.1343,
            ],
        );

        $places = [
            ['city' => $damascus, 'name' => 'Malki', 'latitude' => 33.5140, 'longitude' => 36.2770],
            ['city' => $damascus, 'name' => 'Mazzeh', 'latitude' => 33.5200, 'longitude' => 36.2800],
            ['city' => $damascus, 'name' => 'Abu Rummaneh', 'latitude' => 33.5085, 'longitude' => 36.2710],
            ['city' => $aleppo, 'name' => 'Aziziyah', 'latitude' => 36.1980, 'longitude' => 37.1520],
        ];

        foreach ($places as $place) {
            Places::query()->updateOrCreate(
                [
                    'cities_id' => $place['city']->id,
                    'name' => $place['name'],
                ],
                [
                    'latitude' => $place['latitude'],
                    'longitude' => $place['longitude'],
                ],
            );
        }
    }
}
