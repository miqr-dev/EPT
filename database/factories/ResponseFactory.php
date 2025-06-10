<?php

namespace Database\Factories;

use App\Models\Question; // Moved use statement
use App\Models\TestSessionTest; // Moved use statement
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Response>
 */
class ResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_session_test_id' => TestSessionTest::factory(),
            'question_id' => Question::factory(),
            'answer' => $this->faker->sentence,
            'is_correct' => $this->faker->optional()->boolean,
            'points_awarded' => $this->faker->optional()->numberBetween(0, 10),
        ];
    }
}
