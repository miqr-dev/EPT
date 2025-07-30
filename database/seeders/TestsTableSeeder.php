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
      ['name' => 'BRT-A', 'code' => 'BRT-A'],
      ['name' => 'BRT-B', 'code' => 'BRT-B'],
      ['name' => 'MRT-A', 'code' => 'MRT-A'],
      ['name' => 'MRT-B', 'code' => 'MRT-B'],
      ['name' => 'FPI-R', 'code' => 'FPI-R'],
      ['name' => 'LMT',   'code' => 'LMT'],
    ];

    foreach ($tests as $test) {
      Test::firstOrCreate(['code' => $test['code']], $test);
    }
  }
}
