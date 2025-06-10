<?php

namespace Database\Factories;

use App\Models\Test; // Moved use statement
use App\Models\TestSession; // Moved use statement
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TestSessionTest>
 */
class TestSessionTestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_session_id' => TestSession::factory(),
            'test_id' => Test::factory(),
            'start_time' => $this->faker->optional()->dateTimeThisMonth(),
            'end_time' => $this->faker->optional()->dateTimeThisMonth(),
            'timer_override' => $this->faker->optional()->numberBetween(60, 600),
            'status' => $this->faker->randomElement(['pending', 'active', 'paused', 'completed', 'skipped']),
            'score' => $this->faker->optional()->randomFloat(2, 0, 100),
        ];
    }
}
