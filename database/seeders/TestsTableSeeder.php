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
      ['name' => 'BRT-A', 'code' => 'BRT-A', 'duration' => 30],
      ['name' => 'BRT-B', 'code' => 'BRT-B', 'duration' => 30],
      ['name' => 'MRT-A', 'code' => 'MRT-A', 'duration' => 60],
      ['name' => 'MRT-B', 'code' => 'MRT-B', 'duration' => 60],
      ['name' => 'FPI-R', 'code' => 'FPI-R', 'duration' => 60],
      ['name' => 'LMT',   'code' => 'LMT', 'duration' => 60],
      ['name' => 'BIT-2', 'code' => 'BIT-2', 'duration' => 60],
    ];

    foreach ($tests as $test) {
      Test::firstOrCreate(['code' => $test['code']], $test);
    }
  }
}
