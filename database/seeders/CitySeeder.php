<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        City::create(['name' => 'Erfurt']);
        City::create(['name' => 'Leipzig']);
        City::create(['name' => 'Suhl']);
        City::create(['name' => 'Dresden']);
        City::create(['name' => 'Chemnitz']);
        City::create(['name' => 'Döbeln']);
        City::create(['name' => 'Riesa']);
        City::create(['name' => 'Berlin TBR']);
        City::create(['name' => 'Berlin PP']);
        // Add more as needed
    }
}
