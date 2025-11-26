<?php

namespace Database\Seeders;

use App\Models\Test;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class TestsTableSeeder extends Seeder
{
  public function run(): void
  {
    $tests = [
      ['name' => 'BRT-A', 'code' => 'BRT-A', 'duration' => 6],
      ['name' => 'BRT-B', 'code' => 'BRT-B', 'duration' => 35],
      ['name' => 'MRT-A', 'code' => 'MRT-A', 'duration' => 3],
      ['name' => 'MRT-B', 'code' => 'MRT-B', 'duration' => 30],
      ['name' => 'FPI-R', 'code' => 'FPI-R', 'duration' => 30],
      ['name' => 'LMT',   'code' => 'LMT', 'duration' => 20],
      ['name' => 'BIT-2', 'code' => 'BIT-2', 'duration' => 15],
      ['name' => 'AVEM', 'code' => 'AVEM', 'duration' => 20],
      ['name' => 'Konzentrationstest', 'code' => 'Konzentrationstest', 'duration' => 6],
    ];

    foreach ($tests as $test) {
      Test::firstOrCreate(['code' => $test['code']], $test);
    }
  }
}
