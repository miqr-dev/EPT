<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  public function run(): void
  {
    $this->call([
      CitySeeder::class,
      ProfessionGroupSeeder::class,
      //UserSeeder::class,
      TestsTableSeeder::class
    ]);
  }
}
