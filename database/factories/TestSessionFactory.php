<?php

namespace Database\Factories;

use App\Models\Admin; // Moved use statement
use App\Models\Participant; // Moved use statement
use App\Models\TestBattery; // Moved use statement
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TestSession>
 */
class TestSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'participant_id' => Participant::factory(),
            'test_battery_id' => TestBattery::factory(),
            'admin_id' => Admin::factory(),
            'start_time' => $this->faker->dateTimeThisMonth(),
            'end_time' => $this->faker->optional()->dateTimeThisMonth(),
            'status' => $this->faker->randomElement(['pending', 'active', 'paused', 'completed', 'cancelled']),
        ];
    }
}
