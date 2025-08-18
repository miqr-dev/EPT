<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Normtable;

class NormtableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Normtable::updateOrCreate(
            ['name' => 'BRT-A Rohwert to PR'],
            [
                'description' => 'Maps the raw score (Rohwert) of the BRT-A test to a percentile rank (Prozentrang).',
                'table_data' => [
                    1 => 0, 2 => 0, 3 => 2.5, 4 => 2.5, 5 => 5, 6 => 8.5, 7 => 16,
                    8 => 27, 9 => 34, 10 => 53, 11 => 62, 12 => 75, 13 => 85, 14 => 95, 15 => 99, 16 => 100,
                ],
            ]
        );

        Normtable::updateOrCreate(
            ['name' => 'BRT-A PR to T-Wert'],
            [
                'description' => 'Maps the percentile rank (Prozentrang) of the BRT-A test to a T-score (T-Wert).',
                'table_data' => [
                    ['pr' => 0, 't' => 30], ['pr' => 2, 't' => 30], ['pr' => 5, 't' => 34],
                    ['pr' => 7, 't' => 35], ['pr' => 8, 't' => 36], ['pr' => 16, 't' => 40],
                    ['pr' => 27, 't' => 44], ['pr' => 34, 't' => 46], ['pr' => 53, 't' => 50],
                    ['pr' => 62, 't' => 53], ['pr' => 75, 't' => 56], ['pr' => 85, 't' => 60],
                    ['pr' => 95, 't' => 66], ['pr' => 99, 't' => 73], ['pr' => 100, 't' => 80],
                ],
            ]
        );
    }
}
