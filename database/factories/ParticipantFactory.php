<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'klasse' => $this->faker->optional()->word,
            'age_year' => $this->faker->optional()->numberBetween(6, 18),
            'age_month' => $this->faker->optional()->numberBetween(0, 11),
            'german_level' => $this->faker->optional()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
            'english_level' => $this->faker->optional()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
            'math_level' => $this->faker->optional()->randomElement(['basic', 'intermediate', 'advanced']),
            'math_note' => $this->faker->optional()->randomElement(['1', '2', '3', '4', '5', '6']),
            'schulzweig' => $this->faker->optional()->word,
            'schule' => $this->faker->optional()->company,
            'schulart' => $this->faker->optional()->word,
        ];
    }
}
