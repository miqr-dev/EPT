<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  public function run(): void
  {
    User::create([
      'name' => 'Ara',
      'firstname' => 'Matoyan',
      'email' => 'ara@miqr.de',
      'password' => Hash::make('123qwe!"§'),
      'role' => 'admin',
      'city_id' => 1,
    ]);
    User::create([
      'name' => 'Doe',
      'firstname' => 'John',
      'email' => 'johnd@miqr.de',
      'password' => Hash::make('123qwe!"§'),
      'role' => 'participant',
      'city_id' => 1,
    ]);
    User::create([
      'name' => 'Doe',
      'firstname' => 'Jane',
      'email' => 'janed@miqr.de',
      'password' => Hash::make('123qwe!"§'),
      'role' => 'teacher',
      'city_id' => 1,
    ]);
  }
}
