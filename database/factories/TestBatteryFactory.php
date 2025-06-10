<?php

namespace Database\Factories;

use App\Models\Admin; // Moved use statement to the correct position
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TestBattery>
 */
class TestBatteryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'admin_id' => Admin::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'pause_time_between_tests' => $this->faker->numberBetween(30, 300),
        ];
    }
}
