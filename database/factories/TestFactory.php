<?php

namespace Database\Factories;

use App\Models\Normtable; // Moved use statement
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Test>
 */
class TestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'active' => $this->faker->boolean,
            'scoring_table_id' => Normtable::factory(),
        ];
    }
}
