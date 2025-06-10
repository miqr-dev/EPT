<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Normtable>
 */
class NormtableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'table_data' => json_encode(['score_to_value' => [$this->faker->numberBetween(0,10) => $this->faker->numberBetween(1,5)]]),
        ];
    }
}
