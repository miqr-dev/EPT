<?php

namespace Database\Seeders;

use App\Models\Employed;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EmployedSeeder extends Seeder
{

  public function run(): void
  {
    Employed::create(['name' => 'Berufstätig']);
    Employed::create(['name' => 'ja ']);
    Employed::create(['name' => 'ja- mithelfend im eigenen Betrieb ']);
    Employed::create(['name' => 'Hausfaru / Hausmann']);
    Employed::create(['name' => 'Schüler(in)']);
    Employed::create(['name' => 'Student(in)']);
    Employed::create(['name' => 'inBerfusausbildung']);
    Employed::create(['name' => 'Rentner(in) ']);
    Employed::create(['name' => 'Ruhestand']);
    Employed::create(['name' => 'arbeitslos']);
    Employed::create(['name' => 'ohne Beruf']);
  }
}
