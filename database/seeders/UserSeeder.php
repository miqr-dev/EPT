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
      'name' => 'Admin',
      'firstname' => 'AM',
      'email' => 'admin@email.com',
      'password' => Hash::make('123qwe!"§'),
      'role' => 'admin',
      'city_id' => 1,
    ]);
    User::create([
      'name' => 'Doe',
      'firstname' => 'John',
      'email' => 'john@email.com',
      'password' => Hash::make('123qwe!"§'),
      'role' => 'participant',
      'city_id' => 1,
    ]);
    User::create([
      'name' => 'Doe',
      'firstname' => 'Jane',
      'email' => 'jane@email.com',
      'password' => Hash::make('123qwe!"§'),
      'role' => 'teacher',
      'city_id' => 1,
    ]);
  }
}
